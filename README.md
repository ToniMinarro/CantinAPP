# CantinAPP
Se trata de una aplicación web para la gestión de comandas de un restaurante corporativo. Desarrollada como proyecto de fin de ciclo del CFGS - Desarrollo de Aplicaciones Web.

Este proyecto se enmarca en la necesidad de manejar los pedidos que un restaurante corporativo recibe de sus empleados, así como la información que facilita los cobros del servicio.

Se trata de una empresa con aproximadamente 450 trabajadores, de los cuales, la mayor parte hace uso diario o muy habitual del servicio. Como mínimo, 1/4 parte de la plantilla de la empresa hace uso diario del servicio (datos oficiales), así como también clientes ajenos a la empresa, aunque éstos últimos no estarán contemplados en este sistema. 
Actualmente, tanto la gestión de los pedidos, como el cobro del servicio, es llevado de manera completamente independiente para cada trabajador o departamento, existiendo así departamentos muy organizados y que facilitan el servicio, y otros que prefieren su gestión individual. Es por esto, que queda justificado el desarrollo de este sistema, y enmarcado en el Módulo de Proyecto del ciclo formatico Desarrollo de Aplicaciones Web.



                                                            #DESCRIPCIÓN DEL SISTEMA

El sistema debe ser capaz de permitir a los usuarios (empleados) el manejo de sus pedidos, desde la creación de la manera más sencilla posible, hasta la modificación de la composición de sus pedidos.

Por otra parte, debe de permitir la gestión del servicio por parte del restaurante, principalmente la creación de menús diarios la gestión de la cola de pedidos.

Para la elaboración de este sistema, se ha diseñado y construido una aplicación Web con PHP como lenguaje principal, acompañado de un SGBD MySQL para la persistencia de datos, utilizado mediante la interfaz PDO que ofrece PHP. Por supuesto también se han utilizado las distintas tecnologías que normalmente pueden orquestar un entorno web, como son JavaScript, AJAX, CSS, Bootstrap, etc.

El enfoque del proyecto es mediante el paradigma POO, para lo que se han creado las clases que soportan una posible solución al problema propuesto.

Además de esto, y como filosofía personal, adoptada en base al desarrollo en diferentes frameworks, cada clase contiene la definición de atributos, constructores, métodos, etc., además de la funcionalidad de comunicación directa con la BBDD a través de PDO.

En este sentido, resulta muy sencillo instanciar una clase, pasarle la información que requiere para su construcción, y hacer la misma persistente empleando el mínimo esfuerzo:
