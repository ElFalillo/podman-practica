# Práctica de Podman con Ansible

Este repositorio contiene una práctica independiente para aprender **Podman** y desplegar un laboratorio con **PHP** y **MySQL** mediante un playbook de **Ansible**.

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
- Automatizar despliegues con Ansible.

## ¿Qué es un playbook de Ansible?

Un **playbook** es un fichero YAML donde se describen las tareas que Ansible debe ejecutar en uno o varios servidores.

En este proyecto, el playbook realiza las siguientes tareas:

1. Instala Podman en la máquina gestionada.
2. Crea las carpetas necesarias para los datos y la web.
3. Crea una red Podman llamada `r3con-net`.
4. Despliega un contenedor MySQL.
5. Despliega un contenedor PHP con Apache.
6. Copia una página PHP de prueba.
7. Comprueba que ambos contenedores están en ejecución.

## Estructura del repositorio

```text
podman-practica/
├── README.md
├── inventory.ini
├── desplegar_podman.yml
├── web/
│   └── index.php
└── .gitignore
```

## Requisitos

Necesitas:

- Una máquina controladora con Ansible instalado.
- Una máquina virtual Debian como servidor gestionado.
- Conectividad SSH entre ambas máquinas.
- Un usuario con permisos `sudo` en la máquina gestionada.

## Instalar Ansible

En la máquina controladora:

```bash
sudo apt update
sudo apt install -y python3 python3-pip python3-venv
python3 -m venv ~/ansible-env
source ~/ansible-env/bin/activate
pip install ansible
ansible --version
```

## Configurar SSH

Genera una clave SSH en la máquina controladora:

```bash
ssh-keygen -t ed25519
```

Cópiala al servidor gestionado:

```bash
ssh-copy-id usuario@IP_DEL_SERVIDOR
```

Comprueba la conexión:

```bash
ssh usuario@IP_DEL_SERVIDOR
```

## Configurar el inventario

Edita `inventory.ini` y sustituye la IP y el usuario por los de tu máquina Debian:

```ini
[servidores_podman]
192.168.1.50 ansible_user=tu_usuario
```

## Ejecutar el playbook

Primero, comprueba qué cambios se aplicarían sin modificar el servidor:

```bash
ansible-playbook -i inventory.ini desplegar_podman.yml --check
```

Para ejecutar el despliegue:

```bash
ansible-playbook -i inventory.ini desplegar_podman.yml
```

## Comprobar los contenedores

En la máquina gestionada:

```bash
podman ps
```

Deberías ver los contenedores `r3con-db` y `r3con-web` en ejecución.

Para ver los logs de MySQL:

```bash
podman logs r3con-db
```

Para ver los logs del servidor web:

```bash
podman logs r3con-web
```

## Acceder a la aplicación

Desde la máquina gestionada:

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

## Explicación del playbook

| Parte | Función |
|---|---|
| `hosts: servidores_podman` | Ejecuta las tareas sobre el grupo definido en el inventario. |
| `become: true` | Usa privilegios de administrador cuando son necesarios. |
| `ansible.builtin.apt` | Instala Podman y sus dependencias en Debian. |
| `ansible.builtin.file` | Crea los directorios para la web y la base de datos. |
| `containers.podman.podman_network` | Crea la red `r3con-net`. |
| `containers.podman.podman_container` | Crea y ejecuta los contenedores. |
| `ansible.builtin.copy` | Copia la página PHP al directorio web. |

## Seguridad

Este laboratorio usa contraseñas sencillas solo para practicar. En un entorno real:

- No guardes contraseñas en el repositorio.
- Usa un fichero `.env` excluido por `.gitignore`.
- Usa Ansible Vault para proteger secretos.
- No publiques el puerto de MySQL al exterior.
- Usa contraseñas largas y únicas.
- Mantén las imágenes actualizadas.

## Siguiente paso

Cuando este laboratorio funcione, puedes ampliarlo con:

- Un `Containerfile` para crear una imagen propia de R3con.
- Nginx como proxy inverso delante de PHP-FPM.
- Volúmenes con nombre en lugar de carpetas locales.
- Copias de seguridad automáticas de MySQL.
- Integración con Wazuh, Grafana o Fail2ban.
