.. _api:

API
===

:Release: |version|
:Date: |today|

En los siguienes apartados vamos a definir la interfaz que se utilizará para realizar operaciones
con máquinas virtuales, así como algunas consideraciones previas que deberemos tener en cuenta.

Consideraciones de seguridad
----------------------------

Las peticiones a la API se realizarán a través del protocolo https. Para garantizar que las peticiones
únicamente son realizadas desde los clientes autorizados, vamos a utilizar la negociación de los certificados
en la sesión SSL como medida de protección. Esto quiere decir que el servidor web que procesará las peticiones
a estas urls verificará que el cliente esté utilizando un certificado expedido por la autoridad certificadora
en cuestión. Es importante igualmente que el cliente verifique que el certificado que usa el servidor web 
también proviene de una autoridad certificadora de confianza.

Se expedirán tres juegos de certificados, uno por cada zona. Las zonas seran: administración, zona privada 
(de acceso restringido a los apache) y zona publica (donde las máquinas virtuales notificarán su estado).

Para poder implementar esta estructura de certificados en el servidor ha sido necesario dividir la aplicación
en tres partes, para asignar a cada una su certificado. Las urls para cada parte son:

* Zona pública

	``https://api.sudominio.com``

* Administración

	``https://api_admin.sudominio.com``

* Zona privada

	``https://api_private.sudominio.com``


.. _new:

Creación de una nueva máquina virtual
-------------------------------------

.. method:: https://api_private.sudominio.com/vlab/api/new/PETICION_ID/MINUTOS/MAQUINA_ID/EMAIL/ZONE/

Esta url permite crear máquinas virtuales. Esta petición debe ser lanzada cuando el alumno
decide comenzar una práctica.

.. note::

    Si no se respetan los parámetros exactamente tal y como se refleja en la documentación
    la petición será rechazada y la conexión devolverá un error 404 (página no encontrada)

**Parámetros:**

* PETICION_ID: [10 dígitos] 

    Este identificador lo definirá el autor de la petición, y será su vínculo de comunicación
    con la máquina asociada a esta petición. Cualquier operación relacionada con alguna máquina
    virtual deberá venir acompañada de este identificador. Dicho identificador estará constituido
    únicamente por números (10), y cada máquina virtual debe contar únicamente con uno.

    Ejemplo: ``0123456789``

* MINUTOS: [3 dígitos]

    Tiempo que permanecerá activa la máquina virtual expresado en minutos. Cuando se crea una máquina
    virtual es preciso indicar el tiempo que la máquina deberá permanecer activa. Este tiempo está
    indicado en minutos, y debe ser un valor de 3 dígitos. En el caso de que la cantidad sea inferior a
    cien, debe completarse con ceros la parte de la izquiera hasta llegar a los tres dígitos (090, por
    ejemplo). *Tiempo máximo: 235 minutos. Tiempo mínimo: 15 minutos.*

    Ejemplo: ``120``

* MAQUINA_ID: [10 dígitos]

    Este identificador hace referencia al tipo de máquina virtual que queremos lanzar. El listado de 
    identificadores válidos se puede obtener tanto desde la interfaz de administración (pendiente de 
    documentar) como haciendo uso del método list descrito más abajo.

    Ejemplo: ``0123456789``

* EMAIL: [string]@[string].[string]

    Este identificador hace referencia al email al que se deben notificar los cambios en la  máquina 
    virtual que queremos lanzar.

    Ejemplo: ``nombrealumno@dominio.es``

* ZONE: [9 caracteres]

    Este identificador hace referencia a la zona en la que se debe crear la máquina virtual que queremos
    lanzar. El parametro es opcional, si se indica la zona la máquina se lanzará en dicha zona, sino,
    la máquina se lanzará teniendo en cuenta el orden de prioridad establecido en la administracion.
    En las plataformas LATAM es importante que se utilice este parámetro y que se establezca el valor 
    de la zona a us-west-1.

    Ejemplo: ``us-west-1``

**Respuesta:**

* ESTADO_PETICION: formato JSON

    Ejemplo: ``{"status":"OK","reason":""}``

    Ejemplo: ``{"status":"ERROR","reason":"MAQUINA_ID no valido"}``

**Ejemplo de creación de una nueva máquina virtual:**

``https://api_private.sudominio.com/vlab/api/new/0000000001/180/1111122222/nombrealumno@dominio.es/``

.. _launch:

Notificación de inicio de máquina virtual
-----------------------------------------

.. method:: https://api.sudominio.com/vlab/api/launched/PETICION_ID/

Haciendo uso de esta url una máquina virtual podrá notificar al sistema que ha sido iniciada
con éxito. Desde este momento estará accesible al usuario. Al recibir esta notificación el 
sistema informará al emisor de la solicitud original para que pueda tomar las acciones que
considere necesarias (haciendo uso de la callback_url). Se notificará al usuario mediante email
de que la máquina esta disponible, y se le adjuntarán sus datos de acceso.

.. note::

    Si no se respetan los parámetros exactamente tal y como se refleja en la documentación
    la petición será rechazada y la conexión devolverá un error 404 (página no encontrada)

**Parámetros:**

* PETICION_ID: [10 dígitos] 

    Este identificador lo definirá el autor de la petición, y será su vínculo de comunicación
    con la máquina asociada a esta petición. Cualquier operación relacionada con alguna máquina
    virtual deberá venir acompañada de este identificador. Dicho identificador estará constituido
    únicamente por números (10), y cada máquina virtual debe contar únicamente con uno.

    Ejemplo: ``0123456789``

**Respuesta:**

* ESTADO_PETICION: formato JSON 

    Ejemplo: ``{"status":"OK", "reason":""}``

    Ejemplo: ``{"status":"ERROR","reason":"PETICION_ID no valido"}``

**Ejemplo de notificación de inicio de una máquina virtual:**

``https://api.sudominio.com/vlab/api/launched/0000000001/``

.. _stop:

Detener máquina virtual
-----------------------

.. method:: https://api_private.sudominio.com/vlab/api/stop/PETICION_ID/

Esta url permite detener máquinas virtuales. Por lo general esta url no debería ser invocada
directamente, ya que las máquinas se pararán de forma automática una vez llegada su fecha 
programada de destrucción. No obstante, realizando esta petición es posible detener una máquina
bajo demanda.

.. note::

    Si no se respetan los parámetros exactamente tal y como se refleja en la documentación
    la petición será rechazada y la conexión devolverá un error 404 (página no encontrada)

**Parámetros:**

* PETICION_ID: [10 dígitos] 

    Este identificador lo definirá el autor de la petición, y será su vínculo de comunicación
    con la máquina asociada a esta petición. Cualquier operación relacionada con alguna máquina
    virtual deberá venir acompañada de este identificador. Dicho identificador estará constituido
    únicamente por números (10), y cada máquina virtual debe contar únicamente con uno.

    Ejemplo: ``0123456789``

**Respuesta:**

* ESTADO_PETICION: formato JSON 

    Ejemplo: ``{"status":"OK","reason":""}``

    Ejemplo: ``{"status":"ERROR","reason":"PETICION_ID no valido"}``

**Ejemplo de parada de una máquina virtual:**

``https://api_private.sudominio.com/vlab/api/stop/0000000001/``

.. _stopped:

Notificación de parada de máquina virtual
-----------------------------------------

.. method:: https://api.sudominio.com/vlab/api/stopped/PETICION_ID/

Haciendo uso de esta url una máquina virtual podrá notificar al sistema que ha sido detenida
con éxito. Desde este momento la máquina NO estará accesible al usuario. Al recibir esta notificación el 
sistema informará al emisor de la solicitud original para que pueda tomar las acciones que
considere necesarias (haciendo uso de la callback_url). Además el usuario recibirá una notificación 
mediante email de que la máquina se ha parado.

.. note::

    Si no se respetan los parámetros exactamente tal y como se refleja en la documentación
    la petición será rechazada y la conexión devolverá un error 404 (página no encontrada)

**Parámetros:**

* PETICION_ID: [10 dígitos] 

    Este identificador lo definirá el autor de la petición, y será su vínculo de comunicación
    con la máquina asociada a esta petición. Cualquier operación relacionada con alguna máquina
    virtual deberá venir acompañada de este identificador. Dicho identificador estará constituido
    únicamente por números (10), y cada máquina virtual debe contar únicamente con uno.

    Ejemplo: ``0123456789``

**Respuesta:**

* ESTADO_PETICION: formato JSON 

    Ejemplo: ``{"status":"OK", "reason":""}``

    Ejemplo: ``{"status":"ERROR","reason":"PETICION_ID no valido"}``

**Ejemplo de notificación de parada de una máquina virtual:**

``https://api.sudominio.com/vlab/api/stopped/0000000001/``

.. _imalive:

Notificación de estado de máquina virtual
-----------------------------------------

.. method:: https://api.sudominio.com/vlab/api/imalive/PETICION_ID/

Haciendo uso de esta url una máquina virtual podrá notificar al sistema que ha su estado es online
con éxito. 

.. note::

    Si no se respetan los parámetros exactamente tal y como se refleja en la documentación
    la petición será rechazada y la conexión devolverá un error 404 (página no encontrada)

**Parámetros:**

* PETICION_ID: [10 dígitos]

    Este identificador lo definirá el autor de la petición, y será su vínculo de comunicación
    con la máquina asociada a esta petición. Cualquier operación relacionada con alguna máquina
    virtual deberá venir acompañada de este identificador. Dicho identificador estará constituido
    únicamente por números (10), y cada máquina virtual debe contar únicamente con uno.

    Ejemplo: ``0123456789``

**Respuesta:**

* ESTADO_PETICION: formato JSON

    Ejemplo: ``{"status":"OK", "reason":""}``

    Ejemplo: ``{"status":"ERROR","reason":"PETICION_ID no valido"}``

**Ejemplo de notificación de estado de una máquina virtual:**

``https://api.sudominio.com/vlab/api/imalive/0000000001/``

.. _info:

Información sobre una máquina virtual
-------------------------------------

.. method:: https://api_private.sudominio.com/vlab/api/info/PETICION_ID/

Esta url permite obtener información sobre una máquina virtual. Gracias a ella se podrá
obtener el estado actual (ejecutándose, parada, etc), recoger las credenciales necesarias
para que un usuario pueda conectarse a ella, etc.

.. note::

    Si no se respetan los parámetros exactamente tal y como se refleja en la documentación
    la petición será rechazada y la conexión devolverá un error 404 (página no encontrada)

**Parámetros:**

* PETICION_ID: [10 dígitos] 

    Este identificador lo definirá el autor de la petición, y será su vínculo de comunicación
    con la máquina asociada a esta petición. Cualquier operación relacionada con alguna máquina
    virtual deberá venir acompañada de este identificador. Dicho identificador estará constituido
    únicamente por números (10), y cada máquina virtual debe contar únicamente con uno.

    Ejemplo: ``0123456789``

**Respuesta:**

* INFORMACIÓN MÁQUINA o ESTADO PETICION: formato JSON 

    Formato: JSON([instance,keypair,request,ami])

    Es posible consultar los campos que contienen cada uno de estos objetos en la documentación interna,
    en la sección de modelos (:ref:`models`).

    Ejemplo: ``[{"pk": 1, "model": "api.instance", "fields": {"ami": 1, "real_termination_date": null, "request": 1, "state": "running", "creation_date": "2010-04-21 18:31:40", "instance_id": "i-30b80447", "estimated_termination_date": null, "keypair": 1, "public_dns_name": "ec2-79-125-42-186.eu-west-1.compute.amazonaws.com", "type": "m1.small"}}, {"pk": 1, "model": "api.keypair", "fields": {"region": 1, "creation_date": "2010-04-21 17:39:17", "name": "0000000001", "key": "-----BEGIN RSA PRIVATE KEY-----\nMIIEowIBAAKCAQEA184Kpxhmanq1Os45hTq06pdWCFglxMc1YqcfLur0GGUu...", "fingerprint": "f3:50:7b:cf:29:51:e8:66:21:12:7a:e3:1f:4f:00:15:11:84:16:cc"}}, {"pk": 1, "model": "api.request", "fields": {"sir_id": "sir-66225609", "ext_id": "0000000001", "completed": false, "request_type": "new", "time": 120, "callback_url": null, "virtual_ami": 1, "region": 1}}, {"pk": 1, "model": "api.ami", "fields": {"region": 1, "virtual_ami": 1, "name": "ami-087a517c"}}]``

    Ejemplo: ``{"status":"ERROR","reason":"PETICION_ID no valido"}``

**Ejemplo de consulta de información sobre una máquina virtual:**

``https://api.sudominio.com/vlab/api/info/0000000001/``

.. _list:

Listado de tipos de máquinas virtuales disponibles
--------------------------------------------------

.. method:: https://api_private.sudominio.com/vlab/api/list/

Mediante esta url podremos obtener un listado con todas los identificadores de los
tipos de máquinas virtuales registrados en el sistema.

.. note::

    Si no se respetan los parámetros exactamente tal y como se refleja en la documentación
    la petición será rechazada y la conexión devolverá un error 404 (página no encontrada)

**Parámetros:**

* No recibe parámetros

**Respuesta:**

* LISTADO MÁQUINAS VIRTUALES DISPONIBLES o ESTADO PETICION: formato JSON 

    Ejemplo: ``[{"pk": 1, "model": "api.virtualami", "fields": {"name": "0000000001", "description": "Primera pr\u00e1ctica Oracle 11g "}}, {"pk": 2, "model": "api.virtualami", "fields": {"name": "0000000002", "description": "Segunda pr\u00e1ctica Oracle 11g"}}]``

    Ejemplo: ``{"status":"ERROR","reason":"Ha ocurrido un error"}``

**Ejemplo de consulta de información sobre todas las máquinas virtuales:**

``https://api.sudominio.com/vlab/api/list/``

