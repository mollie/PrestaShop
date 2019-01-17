/**
 * Copyright (c) 2012-2019, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 */
import 'intersection-observer';
import React, { Component } from 'react';
import styled from 'styled-components';
import { throttle } from 'lodash';

import axios from '../../axios';
import { QrStatus } from '../../../globals';
import Spinner from './Spinner';

declare let window: any;

const TitleSpan = styled.span`
&&&& {
  font-size: 20px;
  display: block;
}
`;

const QrImageContainer = styled.div`
&&&& {
  position: relative;
  display: block;
  text-align: ${({ center }: any) => center ? 'center' : 'left'};
  margin: ${({ center }: any) => center ? '0 auto' : 'inherit'};
  height: 240px;
  width: 240px;
}
` as any;

const QrImage = styled.img`
&&&& {
  height: 240px;
  width: 240px;
  position: absolute;
  top: 0;
  left: 0;
}
` as any;

interface IProps {
  title?: string;
  center?: boolean;
}

interface IState {
  enoughSpace: boolean;
  visible: boolean;
  error: boolean;
  image: string;
}

export default class QrCode extends Component<IProps> {
  public readonly state: IState = {
    enoughSpace: false,
    visible: false,
    error: false,
    image: '',
  };
  private initializing = false;
  private ref = React.createRef<HTMLParagraphElement>();
  private resizeHandler = throttle(() => {
    this.checkWindowSize();
  }, 200);
  private observer: IntersectionObserver;
  private mounted = true;

  static clearCache = (): void => {
    for (let key in window.localStorage) {
      if (key.indexOf('mollieqrcache') > -1) {
        window.localStorage.removeItem(key);
      }
    }
  };

  public componentDidMount(): void {
    this.observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        const { isIntersecting, intersectionRatio } = entry;

        if (isIntersecting === true || intersectionRatio > 0) {
          this.setState({ visible: true });
          this.observer.disconnect();
          this.observer = null;
        }
      });
    }, {});
    this.observer.observe(this.ref.current);
    this.checkWindowSize();
    window.addEventListener('resize', this.resizeHandler);
  }

  public componentWillUnmount(): void {
    this.mounted = false;
    if (this.observer != null) {
      this.observer.disconnect();
      this.observer = null;
    }
    try {
      window.removeEventListener('resize', this.resizeHandler);
    } catch (e) {
    }
  }

  public componentDidUpdate(): void {
    const { enoughSpace, visible, image } = this.state;
    if (enoughSpace && visible && !image && !this.initializing) {
      this.initializing = true;
      this.grabAmount().then(this.initQrImage);
    }
  }

  private checkWindowSize = (): void => {
    this.setState({
      enoughSpace: window.innerWidth > 800 && window.innerHeight > 860,
    });
  };

  private pollStatus = (idTransaction: string): void => {
    if (!this.mounted) {
      return;
    }

    setTimeout(async (): Promise<void> => {
      try {
        const { data } = await axios.get(`${window.MollieModule.urls.qrCodeStatus}&transaction_id=${idTransaction}`);
        if (data.status === QrStatus.success) {
          QrCode.clearCache();

          // Never redirect to a different domain
          const a = document.createElement('A') as HTMLAnchorElement;
          a.href = data.href;
          if (a.hostname === window.location.hostname) {
            window.location.href = data.href;
          }
        } else if (data.status === QrStatus.refresh) {
          QrCode.clearCache();
          this.grabNewUrl().then();
        } else if (data.status === QrStatus.pending) {
          this.pollStatus(idTransaction);
        } else {
          console.error('Invalid payment status');
        }
      } catch (e) {
        this.pollStatus(idTransaction);
      }
    }, 5000);
  };

  private grabAmount = async (): Promise<number> => {
    try {
      const { data: { amount } } = await axios.get(window.MollieModule.urls.cartAmount);
      return amount;
    } catch (e) {
      console.error(e);
    }
  };

  private grabNewUrl = async (): Promise<string> => {
    try {
      const { data } = await axios.get(window.MollieModule.urls.qrCodeNew);
      window.localStorage.setItem('mollieqrcache-' + data.expires + '-' + data.amount, JSON.stringify({
        url: data.href,
        idTransaction: data.idTransaction,
      }));
      this.setState({ image: data.href });
      return data.idTransaction;
    } catch (e) {
      console.error(e);
    }
  };

  private initQrImage = (amount: number): void => {
    let url: string = null;
    let idTransaction: string = null;
    if (typeof window.localStorage !== 'undefined') {
      for (let key in window.localStorage) {
        if (key.indexOf('mollieqrcache') > -1) {
          const cacheInfo = key.split('-');
          if (parseInt(cacheInfo[1], 10) > (+new Date() + 60 * 1000) && parseInt(cacheInfo[2], 10) === amount) {
            const item = JSON.parse(window.localStorage.getItem(key));
            const a = document.createElement('A') as HTMLAnchorElement;
            a.href = item.url;
            if (!/\.ideal\.nl$/i.test(a.hostname) || a.protocol !== 'https:') {
              window.localStorage.removeItem(key);
              continue;
            }
            // Valid
            url = item.url;
            idTransaction = item.idTransaction;
            break;
          } else {
            window.localStorage.removeItem(key);
          }
        }
      }

      if (url && idTransaction) {
        this.setState({ image: url });
        this.pollStatus(idTransaction);
      } else {
        this.grabNewUrl().then(this.pollStatus);
      }
    }
  };

  public render() {
    const { title, center } = this.props;
    const { image, enoughSpace, visible, error } = this.state;

    if (!enoughSpace || !visible || error) {
      return <p ref={this.ref} style={{ width: '20px' }}>&nbsp;</p>;
    }

    return (
      <>
        <TitleSpan>{title}</TitleSpan>
        <QrImageContainer center={center}>
          {!image && <Spinner/>}
          {image && <QrImage src={image} center={center}/>}
        </QrImageContainer>
      </>
    );
  }
}
