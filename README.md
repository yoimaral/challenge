# Challenge

En este repositorio encontraras una conecxion con la plataforma placetopay para realizar pagos basicos a travez de web checkout y pse.

## Comenzando 🚀

Estas instrucciones te permitirán obtener una copia del proyecto en funcionamiento en tu máquina local para propósitos de desarrollo y pruebas.\_

# Pre-requisitos 📋

-   Php 7.2.0 con phpCli habilitado para la ejecución de comando.
-   Mysql 5.7.19.
-   Composer

### Instalación 🔧

Para la instalación debes clonar el repositorio en una carpeta preferiblemente vacia.

1. Instalar el controlador de dependencia:

```
❯ composer install
```

2. Crear la base de datos. Se utilizo phpMyAdmin como preferencia:

```
❯ challenge

```

3. Copiar el archivo .env.example y pegarlo en el .env:

```
❯ .env.example .env

# Importante:
- En las variablesde entorno .env debemos agregar el login y el secretKey de PLACETOPAY.
```

4. Laravel Mix para la compilación de los asset.

```
❯ npm install
❯ npm run dev

```


### Ejecutando las pruebas automatizadas para este sistema⚙️

-   Migraciones y alimentación de la base de datos:

```
❯ php artisan migrate:fresh --seed

```

## Construido con 🛠️

```
* El sistema operativo usado fue:

❯ Microsoft

* Consola:

❯ Vs Code


## Versionado 📌

Laravel 7.0


## Autores ✒️

Yoimar Lozano

## Licencia 📄

Este proyecto está bajo la Licencia (yoimaral)
```

