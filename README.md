# Práctica de Podman

Este repositorio contiene una práctica independiente para aprender **Podman** y desplegar un laboratorio con **PHP** y **MySQL** mediante contenedores.

El laboratorio crea dos contenedores:

- `r3con-db`: base de datos MySQL.
- `r3con-web`: servidor web con PHP y Apache.

Ambos contenedores se comunican mediante una red Podman llamada `r3con-net`.

## ¿Qué es Podman?

**Podman** es un motor de contenedores open source. Permite crear, ejecutar y gestionar contenedores de forma similar a Docker, pero sin necesitar un demonio central.

Podman permite ejecutar contenedores como usuario normal, lo que se conoce como **rootless containers**. Esto reduce el riesgo de seguridad, porque el contenedor no necesita ejecutarse con privilegios de administrador.

## ¿Para qué sirve?

Podman sirve para:

- Ejecutar aplicaciones aisladas en contenedores.
- Desplegar servicios como Nginx, PHP, MySQL o bases de datos.
- Crear redes privadas entre contenedores.
- Montar volúmenes para conservar datos.
- Crear imágenes personalizadas mediante `Containerfile`.
- Gestionar contenedores desde la terminal.

## ¿Qué hace esta práctica?

Esta práctica realiza las siguientes tareas:

1. Instala Podman en la máquina Debian.
2. Crea las carpetas necesarias para los datos y la web.
3. Crea una red Podman llamada `r3con-net`.
4. Despliega un contenedor MySQL.
5. Despliega un contenedor PHP con Apache.
6. Crea una página PHP de prueba.
7. Comprueba que ambos contenedores están en ejecución.

## Estructura del repositorio

```text
podman-practica/
├── README.md
├── web/
│   └── index.php
└── .gitignore
```

## Requisitos

Necesitas:

- Una máquina virtual Debian.
- Un usuario con permisos `sudo`.
- Conexión a Internet para descargar las imágenes de los contenedores.

## Instalar Podman

En la máquina Debian:

```bash
sudo apt update
sudo apt install -y podman
podman --version
```

Comprueba que Podman funciona correctamente:

```bash
podman run --rm docker.io/library/hello-world
```

## Crear las carpetas del laboratorio

Crea las carpetas donde se guardarán la web y los datos de MySQL:

```bash
mkdir -p ~/r3con/db
mkdir -p ~/r3con/web
```

Comprueba que se han creado correctamente:

```bash
ls -la ~/r3con
```

## Crear la red Podman

Crea una red privada para que los contenedores puedan comunicarse entre ellos:

```bash
podman network create r3con-net
```

Comprueba que la red se ha creado:

```bash
podman network ls
```

## Desplegar MySQL

Ejecuta el contenedor de MySQL:

```bash
podman run -d --name r3con-db \
  --network r3con-net \
  -e MYSQL_ROOT_PASSWORD=CambiaEstaClaveRoot \
  -e MYSQL_DATABASE=r3con \
  -e MYSQL_USER=r3con_user \
  -e MYSQL_PASSWORD=CambiaEstaClaveUsuario \
  -v ~/r3con/db:/var/lib/mysql:Z \
  docker.io/library/mysql:8.0
```

### Explicación del comando

| Opción | Función |
|---|---|
| `-d` | Ejecuta el contenedor en segundo plano. |
| `--name r3con-db` | Asigna el nombre `r3con-db` al contenedor. |
| `--network r3con-net` | Conecta el contenedor a la red Podman. |
| `-e` | Define variables de entorno. |
| `-v ~/r3con/db:/var/lib/mysql:Z` | Monta la carpeta local como volumen para conservar los datos. |
| `docker.io/library/mysql:8.0` | Imagen oficial de MySQL versión 8.0. |

Comprueba que el contenedor está en ejecución:

```bash
podman ps
```

Para ver los logs de MySQL:

```bash
podman logs -f r3con-db
```

Cuando aparezca un mensaje similar a `ready for connections`, MySQL estará listo. Pulsa `Ctrl + C` para salir de los logs sin detener el contenedor.

## Crear la página PHP

Crea el fichero de la web:

```bash
nano ~/r3con/web/index.php
```

Pega este contenido:

```php
<?php
$host = "r3con-db";
$user = "r3con_user";
$password = "CambiaEstaClaveUsuario";
$database = "r3con";

$conexion = @mysqli_connect($host, $user, $password, $database);

echo "<h1>R3con - Laboratorio Podman</h1>";

echo "<p>Contenedor PHP con Apache ejecutándose correctamente.</p>";

if ($conexion) {
    echo "<p style='color:green;'>Conexión a MySQL correcta.</p>";
    mysqli_close($conexion);
} else {
    echo "<p style='color:red;'>Error de conexión: " . mysqli_connect_error() . "</p>";
}
?>
```

Guarda el fichero con `Ctrl + O`, pulsa Enter y cierra con `Ctrl + X`.

## Desplegar PHP con Apache

Ejecuta el contenedor web:

```bash
podman run -d --name r3con-web \
  --network r3con-net \
  -p 8080:80 \
  -v ~/r3con/web:/var/www/html:Z \
  docker.io/library/php:8.3-apache
```

### Explicación del comando

| Opción | Función |
|---|---|
| `-d` | Ejecuta el contenedor en segundo plano. |
| `--name r3con-web` | Asigna el nombre `r3con-web` al contenedor. |
| `--network r3con-net` | Conecta el contenedor a la misma red que MySQL. |
| `-p 8080:80` | Publica el puerto 80 del contenedor en el puerto 8080 de la máquina. |
| `-v ~/r3con/web:/var/www/html:Z` | Monta la carpeta local con la web dentro del contenedor. |
| `docker.io/library/php:8.3-apache` | Imagen oficial de PHP 8.3 con Apache. |

## Comprobar los contenedores

Para ver los contenedores en ejecución:

```bash
podman ps
```

Deberías ver `r3con-db` y `r3con-web`.

Para ver los logs del servidor web:

```bash
podman logs r3con-web
```

## Acceder a la aplicación

Desde la misma máquina Debian:

```bash
curl http://127.0.0.1:8080
```

Desde otro equipo de la misma red:

```text
http://IP_DEL_SERVIDOR:8080
```

La página comprobará la conexión con MySQL y mostrará si ha sido correcta.

## Comandos útiles de Podman

| Comando | Función |
|---|---|
| `podman ps` | Muestra contenedores en ejecución. |
| `podman ps -a` | Muestra todos los contenedores, incluidos los parados. |
| `podman images` | Muestra las imágenes descargadas. |
| `podman logs nombre_contenedor` | Muestra los logs de un contenedor. |
| `podman exec -it nombre_contenedor bash` | Abre una terminal dentro del contenedor. |
| `podman stop nombre_contenedor` | Detiene un contenedor. |
| `podman start nombre_contenedor` | Arranca un contenedor detenido. |
| `podman rm nombre_contenedor` | Elimina un contenedor. |
| `podman network ls` | Muestra las redes Podman. |
| `podman network inspect r3con-net` | Muestra los detalles de la red. |

## Entrar dentro de MySQL

Puedes acceder a MySQL desde el contenedor:

```bash
podman exec -it r3con-db mysql -u r3con_user -p
```

Cuando pida la contraseña, introduce la que configuraste en `MYSQL_PASSWORD`.

Para comprobar que la base de datos existe:

```sql
SHOW DATABASES;
```

Para salir de MySQL:

```sql
EXIT;
```

## Seguridad

Este laboratorio usa contraseñas sencillas solo para practicar. En un entorno real:

- No guardes contraseñas en el repositorio.
- Usa un fichero `.env` excluido por `.gitignore`.
- No publiques el puerto de MySQL al exterior.
- Usa contraseñas largas y únicas.
- Mantén las imágenes actualizadas.
- Haz copias de seguridad del volumen de MySQL.

## Siguiente paso

Cuando este laboratorio funcione, puedes ampliarlo con:

- Un `Containerfile` para crear una imagen propia de R3con.
- Nginx como proxy inverso delante de PHP-FPM.
- Volúmenes con nombre en lugar de carpetas locales.
- Copias de seguridad automáticas de MySQL.
- Integración con Wazuh, Grafana o Fail2ban.
