# searchCommand
Скрипт для пошуку складених команд (команд, що містять в собі додаткові дані)


Приклад використання

```
POST .../searchCommand.php
```

```
{
  "ssToken":"{{ API SS }}",
  "userId":"{{ userId }}",
  "search":[
    "/command",
    "/action"
  ],
  "delimiter":"_",
  "maxMessage":"20"
}
```

Даний запит шукатиме команди в історії діалогу користувача, які починаються на /command або /action, розділятиме команду на частини по символу _ та надаватиме у відповідь ці окремі частини.
наприклад, відповідь може бути наступного змісту:
```
{
  "state":true,
  "response":{
    "message":"/command_123",
    "needle":"/command"
    "array":[
      "/command",
      "123"
    ],
    "code":"123"
  }
}
```

Даний скрипт може бути корисним, якщо користувач має взаємодіяти з динамічними наборами даних, наданими з таблиці, бази даних або іншого зовнішнього джерела даних. Основна ціль використання скрипта полягає в правильному визначені з яким саме набором даних взаємодіє користувач, якщо йому було надіслано декілька різних наборів даних (використання змінних не підходить, бо в змінних буде зберігатися тільки останій набір даних)


Пояснення даних тіла запиту на скрипт:
- ssToken - токен проекту Smart Sender для отримання історії діалогу користувача
- userId - ідентифікатор користувача, в історії якого потрібно проводити пошук
- search - масив команд для пошуку. Можка вказувати будь-яку кількість
- delimiter - символ (або декілька символів), що розділяють частину пошуку (з масиву search) від частини даних
- maxMessages - максимальна кількість повідомлень серед яких проводити пошук. Рекомендовано використовувати менше 20
- delete - автоматичне видалення знайденого повідомлення. Передайте true (boolean) щоб автоматично видаляти з діалогу повідомлення з командою надіслане користувачем (може бути корисноо для динамічних ботів)
