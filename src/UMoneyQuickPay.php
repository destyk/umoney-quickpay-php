<?php

/**
 * ЮMoney quickpay SDK.
 *
 * @package   destyk/umoney-quickpay-php
 * @author    Nikita Karpov <nikita.karpov.1910@mail.ru>
 * @copyright 2022 (c) DestyK
 * @license   MIT https://raw.githubusercontent.com/destyk/umoney-quickpay-php/main/LICENSE
 */

namespace DestyK\UMoney;

use DestyK\UMoney\Exception;

/**
 * Class for ЮMoney quickpay
 *
 * @see https://yoomoney.ru/docs/payment-buttons/using-api/notifications API Documentation.
 *
 * @property string $secretKey    The secret key is set-only.
 * @property string $usrAlgorithm The custom hash algorithm.
 * @property string $usrSeparator The custom separator.
 */
class QuickPay
{
    /**
     * The default separator.
     *
     * @const string
     */
    const SEPARATOR = '&';

    /**
     * The default hash algorithm.
     *
     * @const string
     */
    const ALGORITHM = 'sha1';

    /**
     * The quickpay url.
     *
     * @const string
     */
    const PAY_URL = 'https://yoomoney.ru/quickpay/confirm.xml';

    /**
     * The secret key.
     *
     * @var string
     */
    private $secretKey;

    /**
     * The custom hash algorithm.
     *
     * @const string
     */
    private $usrAlgorithm;

    /**
     * The custom separator.
     *
     * @const string
     */
    private $usrSeparator;

    /**
     * QuickPay constructor.
     *
     * @param string $key       The secret key.
     * @param string $algorithm The hash algorithm.
     * @param string $separator The separator.
     */
    public function __construct(string $key = '', string $algorithm = self::ALGORITHM, string $separator = self::SEPARATOR)
    {
        $this->secretKey    = $key;
        $this->usrAlgorithm = $algorithm;
        $this->usrSeparator = $separator;
    }

    /**
     * Form creation.
     *
     * @param object|array $data Form inputs.
     *
     * @return array Form inputs & pay url.
     * @throws Exception
     */
    public function createForm(array $data)
    {
        /**
         * Preset required fields.
         */
        $form = array_replace_recursive([
            'receiver'      => null,
            'quickpay-form' => null,
            'paymentType'   => null,
            'targets'       => null,
            'sum'           => null
        ], $data);

        foreach ($form as $key => $param) {
            if (true === is_null($param) || true === empty($param)) {
                throw new Exception(
                    'PARAM_IS_EMPTY:' . $key
                );
            }
        }

        $form += array_filter($data);
        return [
            'form' => $form,
            'url'  => self::PAY_URL
        ];
    }

    /**
     * Checks notification data signature.
     *
     * @param string       $signature        The signature.
     * @param object|array $notificationBody The notification body.
     *
     * @return bool Signature is valid or not.
     * @throws Exception
     */
    public function checkNotificationSignature(string $signature, array $notificationBody)
    {
        /**
         * Preset required fields.
         */
        $body = [
            'notification_type'   => null,
            'operation_id'        => null,
            'amount'              => null,
            'currency'            => null,
            'datetime'            => null,
            'sender'              => null,
            'codepro'             => null,
            'notification_secret' => $this->secretKey,
            'label'               => null
        ];

        foreach ($body as $key => $item) {
            if ('notification_secret' !== $key) {
                if (false === isset($notificationBody[$key])) {
                    throw new Exception(
                        'PARAM_IS_MISSING:' . $key
                    );
                }

                $body[$key] = $notificationBody[$key];
            }
        }

        /**
         * A little bit of magic :)
         */
        $body['amount']       = $this->normalizeAmount($body['amount']);
        $notificationDataKeys = join($this->usrSeparator, $body);
        $ourSignature         = hash($this->usrAlgorithm, $notificationDataKeys);

        return $ourSignature === $signature;
    }

    /**
     * Normalize amount.
     *
     * @param string|float|int $amount The value.
     *
     * @return string The API value.
     */
    private function normalizeAmount(float $amount = 0)
    {
        return number_format(round(floatval($amount), 2, PHP_ROUND_HALF_DOWN), 2, '.', '');
    }
}
