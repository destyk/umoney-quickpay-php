<?php
/**
 * ЮMoney quickpay SDK.
 *
 * @package   DestyK\UMoney
 * @author    Nikita Karpov <nikita.karpov.1910@mail.ru>
 * @license   MIT https://raw.githubusercontent.com/destyk/umoney-quickpay-php/main/LICENSE
 */

namespace DestyK\UMoney;

use Exception;
use ErrorException;

/**
 * Class for ЮMoney quickpay
 *
 * @see https://yoomoney.ru/docs/payment-buttons/using-api/notifications API Documentation.
 *
 * @property string $key The secret key is set-only.
 */
class QuickPay
{
    /**
     * The default separator.
     *
     * @const string
     */
    const VALUE_SEPARATOR = '&';

    /**
     * The default hash algorithm.
     *
     * @const string
     */
    const DEFAULT_ALGORITHM = 'sha1';

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
    protected $secretKey;

    /**
     * QuickPay constructor.
     *
     * @param string $key     The secret key.
     *
     * @throws ErrorException Throw on errors.
     */
    public function __construct($key = '')
    {
        $this->secretKey = (string) $key;
    }

    /**
     * Form creation.
     *
     * @param object|array $data Form inputs.
     *
     * @return array Form inputs & pay url.
     */
    public function createForm(array $data)
    {
        // Preset required fields.
        $form = array_replace_recursive(
            [
                'receiver' => null,
                'quickpay-form' => null,
                'paymentType' => null,
                'targets' => null,
                'sum' => null
            ],
            $data
        );

        foreach($form as $key => $param) {
            if (is_null($param) || empty($param)) {
                throw new ErrorException('PARAM_IS_EMPTY:' . $key);
            }
        }

        $form += array_filter($data);
        return [
            'form' => $form,
            'url' => self::PAY_URL
        ];
    }

    /**
     * Checks notification data signature.
     *
     * @param string       $signature        The signature.
     * @param object|array $notificationBody The notification body.
     *
     * @return bool Signature is valid or not.
     */
    public function checkNotificationSignature($signature, array $notificationBody)
    {
        $body = [
            'notification_type' => null,
            'operation_id' => null,
            'amount' => null,
            'currency' => null,
            'datetime' => null,
            'sender' => null,
            'codepro' => null,
            'notification_secret' => $this->secretKey,
            'label' => null
        ];

        foreach ($body as $key => $item) {
            if ('notification_secret' !== $key) {
                if (false === isset($notificationBody[$key])) {
                    throw new ErrorException('PARAM_IS_MISSING:' . $key);
                }

                $body[$key] = $notificationBody[$key];
            }
        }

        $body['amount'] = $this->normalizeAmount($body['amount']);
        $notificationDataKeys = join(self::VALUE_SEPARATOR, $body);
        $hash = hash(self::DEFAULT_ALGORITHM, $notificationDataKeys);

        return $hash === $signature;
    }

    /**
     * Normalize amount.
     *
     * @param string|float|int $amount The value.
     *
     * @return string The API value.
     */
    public function normalizeAmount($amount=0)
    {
        return number_format(round(floatval($amount), 2, PHP_ROUND_HALF_DOWN), 2, '.', '');

    }
}