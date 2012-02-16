.. _tests:

Tests
=====

URLs para testear algunas de las operaciones del API.


Listado de máquinas virtuales disponibles (list)
------------------------------------------------

(Lanzado desde la plataforma)

* Mostramos el listado de máquinas virtuales disponibles

    https://api_private.sudominio.com/vlab/api/list/

Nueva máquina (new)
-------------------

(Lanzado desde la plataforma)

* Hacemos una petición sin respetar el formato de la url

    https://api_private.sudominio.com/vlab/api/new/0000000XXX001/040/0000000003/

* Intentamos crear una que no existe

    https://api_private.sudominio.com/vlab/api/new/0000000001/040/0000000003/prueba@prueba.es/

* O una que existe con más tiempo del permitido

    https://api_private.sudominio.com/vlab/api/new/0000000001/540/0000000001/prueba@prueba.es/

* Ahora una cuyo virtual ami existe pero que no tiene amis reales asociados

    https://api_private.sudominio.com/vlab/api/new/0000000001/120/0000000002/prueba@prueba.es/

* Creamos una nueva

    https://api_private.sudominio.com/vlab/api/new/0000000001/040/0000000001/prueba@prueba.es/

* Volvemos a intentar crear la misma

    https://api_private.sudominio.com/vlab/api/new/0000000001/040/0000000001/prueba@prueba.es/

Notificamos el arranque de una máquina virtual (launched)
---------------------------------------------------------

(Esto lo realizará cada instancia)

* Notificamos el lanzamiento de una máquina que no existe

    https://api.sudominio.com/vlab/api/launched/0000000008/

* Notificamos el lanzamiento de la máquina que creamos anteriormente

    https://api.sudominio.com/vlab/api/launched/0000000001/

* Volvemos a notificar el lanzamiento de la máquina que creamos anteriormente

    https://api.sudominio.com/vlab/api/launched/0000000001/

Obtener información de una máquina
----------------------------------

(Esto lo realizará la plataforma)

* Solicitamos información de una máquina que no existe

    https://api_private.sudominio.com/vlab/api/info/0000000005/

* Solicitamos información de la máquina que creamos anteriormente

    https://api_private.sudominio.com/vlab/api/info/0000000001/

* Solicitamos información de una máquina sin respetar el formato de la url

    https://api_private.sudominio.com/vlab/api/info/000000000111111/

Paramos una máquina virtual
---------------------------

(Esto lo realizará el proceso que controla el tiempo de vida de cada máquina)

* Solicitamos la parada de una máquina que no existe

    https://api_private.sudominio.com/vlab/api/stop/0000000004/

* Solicitamos la parada de la máquina que creamos anteriormente

    https://api_private.sudominio.com/vlab/api/stop/0000000001/

* Volvemos a solicitar la parada de la máquina que creamos anteriormente

    https://api_private.sudominio.com/vlab/api/stop/0000000001/

Notificamos la parada de una máquina virtual (stopped)
---------------------------------------------------------

(Esto lo realizará cada instancia)

* Notificamos la parada de una máquina que no existe

    https://api.sudominio.com/vlab/api/stopped/0000000008/

* Notificamos la parada de la máquina que creamos anteriormente

    https://api.sudominio.com/vlab/api/stopped/0000000001/

* Volvemos a notificar la parada de la máquina que creamos anteriormente

    https://api.sudominio.com/vlab/api/stopped/0000000001/

