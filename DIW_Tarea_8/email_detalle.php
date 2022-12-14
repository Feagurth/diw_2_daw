<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--
Copyright (C) 2015 Luis Cabrerizo Gómez
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
-->

<?php
//Iniciamos la sesion
session_start();

// Instanciamos los ficheros necesarios
require_once './funciones.php';
require_once './configuracion.inc.php';
require_once './objetos/Email.php';

try {
    // Inicializamos variables
    $error = "";

    // Recupermaos el nombre de usuario
    $nombreUsuario = $_SESSION['nombreUsuario'];

    // Creamos un objeto Email
    $email = new Email(array("id_email" => "", "usuario" => "", "pass" => "", "servidor" => "", "puerto" => "", "seguridad" => "", "autentificacion" => "", "descripcion" => ""));

    // Recuperamos los valores del modo de visión de la página y 
    // del id_email que hemos pasado
    $modo = $_POST['modo'];

    // Recuperamos los valores de email de sesión si están ahí o en su defecto de post
    $id_email = isset($_SESSION['id_email']) ? $_SESSION['id_email'] : $_POST['id_email'];


    // Validamos el usuario
    validarUsuario($_SESSION['user'], $_SESSION['pass']);

    // Creamos un nuevo objeto de acceso a base de datos
    $db = new DB();

    // Comprobamos el modo de la página
    switch ($modo) {
        // Si la página está en modo visualización
        case "V": {
                // Recuperamos la información sobre el email pasándo 
                // su id como parámetro
                $email = $db->recuperarEmail($id_email)[0];

                break;
            }
        // Si la página está en modo añadir
        case "A": {
                // Verificamos si en la información de post tenemos la información 
                // de los botones de confirmanción o de cancelación pulsados.
                // Verificamos si la información del botón es la de cancelar.
                if (isset($_POST['boton']) && $_POST['boton'] === "Cancelar") {
                    // Si cancelamos el añadir un email, pasamos a sesión el 
                    // indice como valor para la página index.php para que cargue 
                    // la plantilla de emails
                    $_SESSION['indice'] = 5;

                    // Navegamos a la pagina index.php
                    header("location:index.php");
                }

                // Comprobamos si la información del botón es la de aceptar
                if (isset($_POST['boton']) && $_POST['boton'] === "Aceptar") {

                    // Comprobamos que la sesión es correcta
                    if (comprobarTokenSesion()) {

                        // Si es así, asignamos la informacón introducida en los inputs 
                        // y que se encuentra en post
                        $email->setId_email($id_email);
                        $email->setUsuario($_POST['usuario']);
                        $email->setPass($_POST['pass']);
                        $email->setServidor($_POST['servidor']);
                        $email->setPuerto($_POST['puerto']);
                        $email->setSeguridad($_POST['seguridad']);
                        $email->setAutentificacion((isset($_POST['autentificacion']) ? "1" : "0"));
                        $email->setDescripcion($_POST['descripcion']);

                        // Validamos los datos introducidos
                        $validacion = validardatosEmail($email);

                        // Comprobamos si hay mensaje de error en la validación
                        if ($validacion === "") {

                            // Si no lo hay, realizamos la insercción pasándo como 
                            // parámetro el objeto Email, dejando la gestión de 
                            // errores de la insercción a las excepciones que se 
                            // puedan lanzar. El id resultante de la insercción, lo 
                            // asignamos a la variable $id_email
                            $id_email = $db->insertarEmail($email);

                            // Asignamos tambien el id_email a la sesión para 
                            // prevenir inserciones extras por refrescos de página
                            $_SESSION['id_email'] = $id_email;

                            // Cambiamos el modo a visor
                            $modo = "V";
                        } else {
                            // Si hay error de validación, copiamos su valor a 
                            // la variable $error
                            $error = $validacion;
                        }
                    } else {
                        // Si la sesión no es válida, recuperamos los datos 
                        // del email para mostrarlos en modo visor
                        $email = $db->recuperarEmail($id_email)[0];
                        $modo = "V";
                    }
                }

                break;
            }

        // Si es una eliminación
        case "E": {

                // Eliminamos el email usando la función adecuada y 
                // pasándo su id como paráemtro
                $db->eliminarEmail($id_email);

                // Tras borrar el email volvemos a la pantalla index.php y para 
                // eso pasamos a sesión el indice como valor para la página 
                // index.php para que cargue la plantilla de emails                
                $_SESSION['indice'] = 5;

                // Navegamos a index.php
                header("location:index.php");

                break;
            }
        // Si la página se carga en modo modificación
        case "M": {

                // Comprobamos si ha pulsado el botón de confirmación o cancelación
                if (isset($_POST['boton'])) {

                    // Si se ha pulsado, vemos si ha sido el de cancelar
                    if ($_POST['boton'] === "Cancelar") {

                        // De ser así, recuperamos los datos origianles del 
                        // email, para que se sobreescriban sobre cualquier 
                        // moficación que haya podido hacer el usuario
                        $email = $db->recuperarEmail($id_email)[0];

                        // Cambiamos el modo de la página a visualización
                        $modo = "V";
                    }

                    // Si se ha pulsado el botón de aceptar
                    if ($_POST['boton'] === "Aceptar") {
                        // Comprobamos que la sesión es correcta
                        if (comprobarTokenSesion()) {

                            // Asignamos la informacón introducida en los inputs 
                            // y que se encuentra en post
                            $email->setId_email($id_email);
                            $email->setUsuario($_POST['usuario']);
                            $email->setPass($_POST['pass']);
                            $email->setServidor($_POST['servidor']);
                            $email->setPuerto($_POST['puerto']);
                            $email->setSeguridad($_POST['seguridad']);
                            $email->setAutentificacion((isset($_POST['autentificacion']) ? "1" : "0"));
                            $email->setDescripcion($_POST['descripcion']);

                            // Realizamos la validación de los datos
                            $validacion = validardatosEmail($email);

                            // Comprobamos si la validación ha generado algún 
                            // mensaje de error
                            if ($validacion === "") {

                                // Si no hay mensaje de error, realizamos la modificación 
                                // pasándo como parámetro el objeto  Email, dejando 
                                // la gestión de errores de la modificación a las excepciones 
                                // que se puedan lanzar
                                $db->modificarEmail($email);

                                // Cambiamos el modo a visor
                                $modo = "V";
                            } else {
                                // Si hay error de validación, copiamos su valor a 
                                // la variable $error                            
                                $error = $validacion;
                            }
                        } else {
                            // Si la sesión no es válida, recuperamos los datos 
                            // del email para mostrarlos en modo visor
                            $email = $db->recuperarEmail($id_email)[0];
                            $modo = "V";
                        }
                    }
                } else {
                    // Si no se ha pulsado ningún botón nos limitamos a 
                    // recuperar los datos del email y mostarselos al usuario
                    $email = $db->recuperarEmail($id_email)[0];
                }
                break;
            }
    }
} catch (Exception $ex) {
    // Recuperamos el mensaje de error
    $error = $ex->getMessage();

    // Cambiamos el modo de la página a visualización
    $modo = "V";
}
?>


<html xmlns="http://www.w3.org/1999/xhtml" lang="es" xml:lang="es">
    <head>
        <title>Detalle E-Mail</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link type = "text/css" rel = "stylesheet" href = "./estilos.css"/>
        <script type="text/javascript" src="HTTP://code.jquery.com/jquery-latest.js"></script>
        <script type="text/javascript" src="scripts/funciones.js"></script>        
        <script type="text/javascript" src="scripts/email_detalle.js"></script>
    </head>
    <body>
        <div class="cabecera" id="index" >
            <h1>Gestión Documental</h1>
        </div>
        <div>
            <?php
            include './menu.php';
            if (!isset($_SESSION['token'])) {
                $_SESSION['token'] = generarTokenSesion();
            }
            ?>
        </div>
        <div id="cuerpo">      
            <div id="botonera">
                <h2>Detalle de E-Mail</h2>
                <form id="añadir" action='email_detalle.php' method='post' >
                    <input type='submit' tabindex="8" value='Añadir E-Mail' title="Pulse para anañadir un nuevo E-mail"  <?php echo deshabilitarBotonesPorModo($modo) ?> />
                    <input class='oculto' name='añadir' type='hidden' value='0' />
                    <input class='oculto' name='modo' type='hidden' value='A' />
                    <input class='oculto' name='id_email' type='hidden' value='0' />
                </form>

                <form id="modificar" action='email_detalle.php' method='post' >
                    <input type='submit' tabindex="8" value='Modificar E-Mail' title="Pulse para modificar el E-Mail actual"  <?php echo deshabilitarBotonesPorModo($modo) ?> />
                    <input class='oculto' name='modificar' type='hidden' value='<?php echo $id_email ?>' />
                    <input class='oculto' name='modo' type='hidden' value='M' />
                    <input class='oculto' name='id_email' type='hidden' value='<?php echo $id_email ?>' />
                </form>
                <form id="eliminar" action='email_detalle.php' method='post' >
                    <input type='submit' tabindex="9" value='Eliminar E-Mail' title="Pulse para eliminar el E-Mail actual"  <?php echo deshabilitarBotonesPorModo($modo) ?> />
                    <input class='oculto' name='modo' type='hidden' value='E' />
                    <input class='oculto' name='id_email' type='hidden' value='<?php echo $id_email ?>' />
                </form>
            </div>
            <div id="detalle">
                <form action="email_detalle.php" method="post">
                    <input type='hidden' name='token' id='token' value='<?php echo $_SESSION["token"] ?>'/>
                    <label id="lblDescripcion" for="descripcion">Descripcion&nbsp;</label>
                    <input  tabindex="10" title="Introduzca una descripción para el servidor de E-Mail" type="text" name="descripcion" id="descripcion" maxlength="55" value="<?php if ($email !== NULL) echo $email->getDescripcion() ?>" <?php echo deshabilitarPorModo($modo) ?> />
                    <label id="lblUsuario" for="usuario">Usuario&nbsp;</label>
                    <input tabindex="11" title="Introduzca el usuario del servidor de E-Mail" type="text" name="usuario" id="usuario" maxlength="32" value="<?php if ($email !== NULL) echo $email->getUsuario() ?>" <?php echo deshabilitarPorModo($modo) ?> />
                    <label id="lblPass" for="pass">Contraseña&nbsp;</label>
                    <input tabindex="12" title = "Introduzca la contraseña del servidor de E-Mail" type="password" name="pass" id="pass" maxlength="32" value="<?php if ($email !== NULL) echo $email->getPass() ?>" <?php echo deshabilitarPorModo($modo) ?> />
                    <br />                                        
                    <label id="lblServidor" for="servidor">Servidor&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                    <input tabindex="13" title="Introduzca un servidor de salida SMTP" type="text" name="servidor" id="servidor" maxlength="30" value="<?php if ($email !== NULL) echo $email->getServidor() ?>" <?php echo deshabilitarPorModo($modo) ?> />
                    <label id="lblPuerto" for="puerto">Puerto&nbsp;&nbsp;</label>
                    <input tabindex="14" title="Introduzca el puerto del servidor de E-Mail" type="text" name="puerto" id="puerto" maxlength="5" value="<?php if ($email !== NULL) echo $email->getPuerto() ?>" <?php echo deshabilitarPorModo($modo) ?> />                    
                    <label id="lblSeguridad" for="seguridad">Tipo de Seguridad&nbsp;</label>
                    <select tabindex="15" title="Seleccione el tipo de seguridad de la cuenta de E-Mail" name="seguridad" id="seguridad" <?php echo deshabilitarPorModo($modo) ?> >
                        <option <?php if ($email->getSeguridad() === "NINGUNA") echo "selected=\"selected\" " ?> value="NINGUNA">Ninguna</option>
                        <option <?php if ($email->getSeguridad() === "TLS") echo "selected=\"selected\" " ?> value="TLS">TLS</option>
                        <option <?php if ($email->getSeguridad() === "SSL") echo "selected=\"selected\" " ?> value="SSL">SSL</option>
                    </select>     
                    <input type="checkbox" name="autentificacion" id="autentificacion" title="Marque si el servidor requiere autentificación" tabindex="16" <?php if ($email->getAutentificacion() === "1") echo "checked=\"checked\" " ?> <?php echo deshabilitarPorModo($modo) ?> />
                    <label id="lblAutentificacion" for="autentificacion">Requiere autentificación</label>
                    <br />                                        
                    <?php
                    // Comprobamos el modo en el que está la página. Si está en 
                    // modo Modificación o Adicción, creamos un botón de aceptar 
                    // modificaciones y otro de cancelarlas
                    if ($modo === "A" || $modo === "M") {

                        // Creamos el botón de aceptar
                        echo "<input tabindex='17' name='boton' id='aceptar' type='submit' value='Aceptar' title='Pulse para confirmar las modificaciones' />";

                        // Creamos el botón de cancelar
                        echo "<input tabindex='18' name='boton' id='cancelar' type='submit' value='Cancelar' title='Pulse para cancelar las modificaciones' />";

                        // Creamos dos objetos ocultos para reenviar la información del modo de la página y del identificador del email
                        echo "<input class='oculto' name='id_email' type='hidden' value='$id_email' />";
                        echo "<input class='oculto' name='modo' type='hidden' value='$modo' />";
                    }
                    ?>      
                </form>               
                <div class="error">
                    <p><?php echo $error ?></p>
                </div>
            </div>            
        </div>
        <div class="modal" ><!-- Posicionar al final del cuerpo de la página --></div>
    </body>
</html>