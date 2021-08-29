# ЮMoney API QuickPay PHP

PHP SDK для реализации быстрых платежей через систему ЮMoney.

## Установка и подключение

Установка с помощью [composer](https://getcomposer.org/download/):

```bash
$ composer require destyk/umoney-quickpay-php
```

## Документация

**Создание платёжной формы**: https://yoomoney.ru/docs/payment-buttons/using-api/forms <br>
**HTTP-уведомления о поступающих платежах**: https://yoomoney.ru/docs/payment-buttons/using-api/notifications

## Создание платёжной формы

Для использования SDK требуется `secretKey`, получить можно [здесь](https://yoomoney.ru/transfer/myservices/http-notification).

```php
<?php

$secretKey = 'saoZflUalRvI************';

try {
    $quickPay = new DestyK\UMoney\QuickPay($secretKey);
    $form = $quickPay->createForm([
        'receiver' => 410024568******,
        'quickpay-form' => 'shop',
        'paymentType' => 'MC',
        'targets' => 'Тестовый платёж',
        'sum' => 500
    ]);
    
    // Url для отправки данных методом POST: $form['url']
    // Данные формы для отправки: $form['form']
} catch(ErrorException $e) {
    echo $e->getMessage();
}
?>
```

## Проверка подписи

Каждая операция по зачислению средств на Ваш кошелёк ЮMoney провоцирует HTTP-уведомление от серверов ЮMoney (если включено в настройках). Каждый такой запрос сопровождается подписью `sha1_hash`. Формирование своей подписи для сверки с пришедшей:


```php
<?php

$sha1_hash = $_POST['sha1_hash'];
$body = $_POST;
$secretKey = 'saoZflUalRvI************';

try {
    $quickPay = new DestyK\UMoney\QuickPay($secretKey);
    // true, если подписи идентичны, false - если нет
    $result = $quickPay->checkNotificationSignature($sha1_hash, $body);
} catch(ErrorException $e) {
    echo $e->getMessage();
}
?>
```

## Требования

* **PHP v5.6.0** или выше
* расширение PHP **json**

## Лицензия

[MIT](LICENSE)