.. _intro:

Introducción
============

:Release: |version|
:Date: |today|

Objetivo del proyecto
---------------------

Nuestro negocio está enfocado principalmente a la teleformación. Contamos con una plataforma que sirve como base para que nuestros alumnos interactúen con todo el material que ponemos a su disposición: cursos multimedia, exámenes, ejercicios, herramientas de interacción con los miembros del curso (correo, foros, chat, agenda), podcasts, etc. Aunque este paquete formativo suele ser más que suficiente para muchas de las materias en las que solemos ofrecer formación, creemos que hay otras en las que un añadido podría resultar fundamental para que el aprovechamiento del curso -y en consecuencia la calidad de la formación- llegue a ser máximo.
Pensemos en materias como cursos de ciertos sistemas operativos, o gestores de bases de datos. Todos los alumnos no cuentan con un equipo en el que puedan (o quieran) instalar un segundo sistema operativo, o quizás no sea lo suficientemente potente como para poder levantar la última versión de un potente gestor de bases de datos. Además, aún contando con el equipamiento necesario, no siempre resulta sencillo preparar el entorno para ir realizando pequeñas pruebas adecuadas al nivel de aprendizaje del alumno.
Y aquí es donde hace acto de aparición el nuevo elemento formativo que queremos añadir a nuestros planes: el laboratorio virtual (virtual lab).

En qué consiste
---------------

La idea es sencilla. Cuando el alumno acceda a su plataforma de teleformación encontrará una nueva opción, los virtual labs. En este apartado el alumno dispondrá de un listado de prácticas, asociadas a los diferentes temas que compongan el curso. Cuando el alumno considere que está preparado para realizarlas, tan sólo deberá hacer click en el botón correspondiente y toda la maquinaría se pondrá en marcha. ¿Qué ocurrirá realmente? De forma transparente al usuario, el sistema localizará una imagen de una máquina que cuenta con todo lo necesario para que el alumno pueda realizar la práctica, sin tener que preparar absolutamente nada del entorno ni configurar nada. A efectos prácticos, en algún lugar del mundo se habrá preparado un servidor virtual que contará con todo lo que el alumno necesita, únicamente a su disposición. La máquina no sólo contará con el software necesario instalado, sino que éste también estará adecuado a cada situación particular: datos necesarios, configuraciones, etc. Una vez localizada, se iniciará una máquina con dicha imagen y se configurará lo necesario (todo de forma automática, casi instantáneo) para que el alumno pueda conectarse a ella de forma remota y realizar los ejercicios correspondientes. De hecho, al hacer click en la práctica correspondiente el alumno podrá visualizar los datos de conexión de la máquina, que estará disponible en escasos minutos (tiempo de arranque del sistema). Una vez concluida la práctica la máquina será destruida quedando los recursos que utilizaba liberados.  

Implementación
--------------

Como es de suponer, son muchos los aspectos técnicos que hay que considerar a la hora de implementar un sistema de estas dimensiones. Veamos algunos de ellos.

* **Escalabilidad**. Lo primero y más importante es contar con la infraestructura necesaria para poder ofrecer estos servicios, ya que los requisitos tanto es aspectos de hardware como en materia de comunicaciones serán muy elevados. La solución por la que nos hemos decantado es trabajar con determinados proveedores en "la nube".

* **Comunicación entre los distintos componentes**. En principio destaquemos principalmente dos componentes: las plataformas de teleformación y estas nuevas máquinas virtuales. Para abstraer al máximo el nuevo sistema de la plataforma de teleformación, y hacer que la posible migración a posibles nuevos proveedores de infraestructura sea lo más sencilla posible, vamos a desarrollar un componente (:ref:`api`) intermedio que se encargará de la gestión de estas máquinas. Este componente ofrecerá un pequeño servicio web a las plataformas de teleformación para que éstas, bajo demanda, puedan solicitar la creación (:ref:`new`) y destrucción (:ref:`stop`) de laboratorios cuando lo considere necesario (normalmente bajo demanda del usuario). También habrá que desarrollar el nuevo apartado en las plataformas de teleformación para que, haciendo uso del servicio mencionado anteriormente, puedan gestionar las necesidades de los alumnos de forma integrada en su habitual espacio de trabajo.

* **Moldes para la creación de máquinas**. Cuando el alumno solicita realizar una práctica, el sistema debe levantar una máquina a partir de un molde. Quizás ésta sea una labor en la que debe de pesar más el aspecto pedagógico que el técnico, ya que es muy importante que cada uno de los moldes que vayamos creando se ajusten perfectamente a las necesidades formativas del alumno en cada momento (ajustarse a la línea de tiempo del curso, a los contenidos que se van mostrando en los cursos multimedia, etc). Para conseguir esto es importante que sean creados y configurados por expertos en la materia. Una vez listos, se integrarán con los componentes mencionados anteriormente para que el engranaje comience a funcionar de forma totalmente automatizada.
