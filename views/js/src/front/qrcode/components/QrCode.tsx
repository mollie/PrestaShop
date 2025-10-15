/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
import 'intersection-observer';
import React, { ReactElement, useEffect, useRef, useState } from 'react';
import styled from 'styled-components';
import { throttle } from 'lodash';

import LoadingDotsCentered from '@shared/components/LoadingDotsCentered';
import axios from '@shared/axios';
import { QrStatus } from '@shared/globals';

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

export default function QrCode({ title, center }: IProps): ReactElement<{}> {
  const [enoughSpace, setEnoughSpace] = useState<boolean>(false);
  const [visible, setVisible] = useState<boolean>(false);
  const [error] = useState<boolean>(false);
  const [image, setImage] = useState<string>('');
  const [initializing, setInitializing] = useState<boolean>(false);
  const [mounted, setMounted] = useState<boolean>(true);

  function _clearCache(): void {
    for (let key in window.localStorage) {
      if (key.indexOf('mollieqrcache') > -1) {
        window.localStorage.removeItem(key);
      }
    }
  }

  function _checkWindowSize(): void {
    setEnoughSpace(window.innerWidth > 800 && window.innerHeight > 860);
  }

  async function _grabNewUrl(): Promise<string> {
    try {
      const { data } = await axios.get(window.MollieModule.urls.qrCodeNew);
      window.localStorage.setItem('mollieqrcache-' + data.expires + '-' + data.amount, JSON.stringify({
        url: data.href,
        idTransaction: data.idTransaction,
      }));
      setImage(data.href);
      return data.idTransaction;
    } catch (e) {
      console.error(e);
    }
  }

  function _pollStatus(idTransaction: string): void {
    if (!mounted) {
      return;
    }

    setTimeout(async (): Promise<void> => {
      try {
        const { data } = await axios.get(`${window.MollieModule.urls.qrCodeStatus}&transaction_id=${idTransaction}`);
        if (data.status === QrStatus.success) {
          _clearCache();

          // Never redirect to a different domain
          const a = document.createElement('A') as HTMLAnchorElement;
          a.href = data.href;
          if (a.hostname === window.location.hostname) {
            window.location.href = data.href;
          }
        } else if (data.status === QrStatus.refresh) {
          _clearCache();
          _grabNewUrl().then();
        } else if (data.status === QrStatus.pending) {
          _pollStatus(idTransaction);
        } else {
          console.error('Invalid payment status');
        }
      } catch (e) {
        _pollStatus(idTransaction);
      }
    }, 5000);
  }

  async function _grabAmount(): Promise<number> {
    try {
      const { data: { amount } } = await axios.get(window.MollieModule.urls.cartAmount);
      return amount;
    } catch (e) {
      console.error(e);
    }
  }

  function _initQrImage(amount: number): void {
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
        setImage(url);
        _pollStatus(idTransaction);
      } else {
        _grabNewUrl().then(_pollStatus);
      }
    }
  }

  const ref = useRef<| null>(null);

  const resizeHandler = throttle(() => {
    _checkWindowSize();
  }, 200);

  useEffect(() => {
    setMounted(true);
    let observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        const { isIntersecting, intersectionRatio } = entry;

        if (isIntersecting === true || intersectionRatio > 0) {
          setVisible(true);
          observer.disconnect();
          observer = null;
        }
      });
    }, {});
    observer.observe(ref.current);
    _checkWindowSize();
    window.addEventListener('resize', resizeHandler);

    return () => {
      setMounted(false);
      if (observer != null) {
        observer.disconnect();
        observer = null;
      }

      window.removeEventListener('resize', resizeHandler);
    }
  }, []);

  useEffect(() => {
    if (enoughSpace && visible && !image && !initializing) {
      setInitializing(true);
      _grabAmount().then(_initQrImage);
    }
  }, [enoughSpace, visible, image, initializing]);

  if (!enoughSpace || !visible || error) {
    return <p ref={ref} style={{ width: '20px' }}>&nbsp;</p>;
  }

  return (
    <>
      <TitleSpan>{title}</TitleSpan>
      <QrImageContainer center={center}>
        {!image && <LoadingDotsCentered/>}
        {image && <QrImage src={image} center={center}/>}
      </QrImageContainer>
    </>
  );
}
