Тестове завдання для Honeytech!

Реалізація blog на symfony з використанням mysql, scss, docker, phpunit, swagger.

Для запуску проекту з використанням docker необхідно виконати команду - docker-compose up -d.

Щоб переглянути всі запущені docker контейнери, необхідно виконати команду - docker ps.

Щоб потрапити всередину docker контейнера, необхідно виконати команду - docker-compose exec -it CONTAINER_NAME bash 

Docker автоматично завантажить і встановить всі необхідні залежності для composer і npm.

Щоб заново зібрати front-end, необхідно виконати команду всередині docker контейнера PHP - npm run dev.

Щоб запустити тести, необхідно виконати команду всередині docker контейнера PHP - php bin/phpunit

Credentials:

MySQL: 

Для використання mysql всередині docker контейнера:
host - mysql;port - 3306

Для використання mysql зовні docker контейнера:
host - localhost;port - 2910

username - root;password - PassWordRoot10!

username - blog;password - PassWord10!

NGINX:

Для використання nginx всередині контейнера docker:

host - localhost;port - 80

Для використання nginx поза контейнером docker:
host - localhost;port - 2908

Посилання на веб-додаток:
http://localhost:2908

Документація для api:
http://localhost:2908/api/doc

