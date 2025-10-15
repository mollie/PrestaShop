import React, {ReactElement, useCallback} from 'react';
import cx from 'classnames';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {faCircleNotch, faTimes, faTruck, faUndo} from '@fortawesome/free-solid-svg-icons';
import {useMappedState} from 'redux-react-hook';

import { IMollieAmount, IMollieOrderLine } from '@shared/globals';

interface IProps {
    line: IMollieOrderLine;
    loading: boolean;
    shipLine: Function;
    cancelLine: Function;
    refundLine: Function;
    availableRefundAmount: IMollieAmount;
}

export default function OrderLinesTableActions({line, loading, shipLine, cancelLine, refundLine, availableRefundAmount }: IProps): ReactElement<{}> {
    const {config: {legacy}, translations }: Partial<IMollieOrderState> = useCallback(useMappedState((state: IMollieOrderState): any => ({
        translations: state.translations,
        config: state.config
    })), []);

    const isRefundable = (): boolean => line.refundableQuantity >= 1 && parseFloat(availableRefundAmount.value) > 0.0
    const isCancelable = (): boolean => line.cancelableQuantity >= 1

    let shipButton = (
        <button
            style={{
                cursor: (loading || line.shippableQuantity < 1 || line.type === 'discount') ? 'not-allowed' : 'pointer',
                width: legacy ? '100px' : 'auto',
                textAlign: legacy ? 'left' : 'inherit',
                opacity: ((loading || !isRefundable()) && legacy) ? 0.8 : 1,
            }}
            className={cx({'btn': !legacy, 'btn-default': !legacy})}
            title=""
            disabled={loading || line.shippableQuantity < 1 || line.type === 'discount'}
            onClick={() => shipLine([line])}
        >
            {legacy && <img
                src="../img/admin/delivery.gif"
                alt={translations.ship}
                style={{
                    filter: (loading || line.shippableQuantity < 1) ? 'grayscale(100%)' : undefined,
                    WebkitFilter: (loading || line.shippableQuantity < 1) ? 'grayscale(100%)' : undefined,
                }}
            />}
            {!legacy && <FontAwesomeIcon icon={!loading ? faTruck : faCircleNotch} spin={loading}/>} {translations.ship}
        </button>
    );

    const refundButton = legacy ? (
        <button
            style={{
                cursor: (loading || !isRefundable() || line.type) === 'discount' ? 'not-allowed' : 'pointer',
                width: '100px',
                textAlign: 'left',
                opacity: (loading || !isRefundable() || line.type === 'discount') ? 0.8 : 1,
            }}
            title=""
            disabled={loading || !isRefundable() || line.type === 'discount'}
            onClick={() => refundLine([line])}
        >
            <img
                src="../img/admin/money.gif"
                alt={translations.refund}
                style={{
                    filter: (loading || !isRefundable() || line.type === 'discount') ? 'grayscale(100%)' : undefined,
                    WebkitFilter: (loading || !isRefundable() || line.type === 'discount') ? 'grayscale(100%)' : undefined,
                }}
            /> {translations.refund}
        </button>
    ) : (
        <a
            style={{
                cursor: (loading || !isRefundable() || line.type === 'discount') ? 'not-allowed' : 'pointer',
                opacity: (loading || !isRefundable() || line.type === 'discount') ? 0.8 : 1,
            }}
            onClick={() => isRefundable() && refundLine([line])}
            role="button"
        >
            <FontAwesomeIcon icon={!loading ? faUndo : faCircleNotch} spin={loading}/> {translations.refund}
        </a>
    );

    const cancelButton = legacy ? (
        <button
            style={{
                cursor: line.cancelableQuantity < 1 ? 'not-allowed' : 'pointer',
                width: '100px',
                textAlign: 'left',
                opacity: (loading || !isRefundable() || line.type === 'discount') ? 0.8 : 1,
            }}
            title=""
            disabled={loading || line.cancelableQuantity < 1 || line.type === 'discount'}
            onClick={() => cancelLine([line])}
        >
            <img
                src="../img/admin/disabled.gif"
                alt={translations.cancel}
                style={{
                    filter: (loading || line.cancelableQuantity < 1 || line.type === 'discount') ? 'grayscale(100%)' : undefined,
                    WebkitFilter: (loading || line.cancelableQuantity < 1 || line.type === 'discount') ? 'grayscale(100%)' : undefined,
                }}
            /> {translations.cancel}
        </button>
    ) : (
        <a
            style={{
                cursor: (loading || line.cancelableQuantity < 1 || line.type === 'discount') ? 'not-allowed' : 'pointer',
                opacity: (loading || line.cancelableQuantity < 1 || line.type === 'discount') ? 0.8 : 1,
            }}
            onClick={() => isCancelable() && cancelLine([line])}
            role="button"
        >
            <FontAwesomeIcon icon={!loading ? faTimes : faCircleNotch} spin={loading}/> {translations.cancel}
        </a>
    );

    const buttonGroup = legacy ? (
        <div>
            {shipButton}<br/>
            {refundButton}<br/>
            {cancelButton}
        </div>
    ) : (
        <div className={cx({
            'btn-group': !legacy,
        })}
        >
            {shipButton}
            <button
                type="button"
                className={cx({
                    'btn': !legacy,
                    'btn-default': !legacy,
                    'dropdown-toggle': !legacy,
                })}
                data-toggle={legacy ? null : 'dropdown'}
                disabled={loading || (!isRefundable() && line.cancelableQuantity < 1) || line.type === 'discount'}
            >
                <span className="caret">&nbsp;</span>
            </button>
            <ul className="dropdown-menu">
                <li>
                    {refundButton}
                </li>
                <li>
                    {cancelButton}
                </li>
            </ul>
        </div>
    );

    return (
        <div className={cx({'btn-group-action': !legacy})}>
            {buttonGroup}
        </div>
    );
}
