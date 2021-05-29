# PHP
Ema Tracker: Programa que identifica cruces de Emas 8 y 55, mediante datos obtenidos con Api de binance y envia mensajes a un canal de telegram.
- Se requiere rellenar datos de Api de binance, ademas de chat_id y telegram token en el archivo config.php.
- Tiene dependencias externas, de forma que hay que correr composer en la instalacion del proyecto.
- El archivo update_exchange.php se debe ejecutar como servicio una vez al dia, para actualizar datos de los pares.
- El archivo ema_tracker_db.sql, tiene la base de datos mysql, que debe ser importada en el servidor.

Este proyecto se realizó con:
- PHP 7.0
- mysql 8.0
