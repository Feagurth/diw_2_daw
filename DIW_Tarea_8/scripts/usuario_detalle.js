/* 
 * Copyright (C) 2015 Luis Cabrerizo Gómez
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

$('document').ready(inicio);

/**
 * Función cuya finalidad es definir los eventos del formulario e inicializar 
 * las variables que sean necesarias para el buen funcionamiento de los métodos 
 * que se encuentran en el archivo
 * @returns {undefined}
 */
function inicio()
{
    // Definimos un evento los botones de añadir, modificar y eliminar
    $("div#botonera form#añadir > input[type='submit']").click(habilitarAñadirModificar);
    $("div#botonera form#modificar > input[type='submit']").click(habilitarAñadirModificar);
    $("div#botonera form#eliminar > input[type='submit']").click(eliminarRegistro);

    // Recuperamos datos que tengamos en los controles
    id_usuario = $("div#botonera form#eliminar > input[name='id_usuario']").val();
    usuario = $("div#detalle form input#user").val();
    contraseña = $("div#detalle form input#pass").val();
    nombre = $("div#detalle form input#nombre").val();

    // Comprobamos si ya está creado el botón de cancelar. De ser así, la 
    // pantalla se ha cargado desde la ventana principal pulsando el botón de 
    // nuevo registro
    if ($("#detalle form #cancelar").length)
    {
        modo = "A";

        // Asignamos una función al evento click del botón de cancelar
        $("#detalle form").off("click", "#cancelar").on("click", "#cancelar", cancelarOperacion);

        // Asignamos una función al evento click del botón de aceptar
        $("#detalle form").off("click", "#aceptar").on("click", "#aceptar", aceptarOperacion);

    }

    $(document).on({
        ajaxStart: function () {
            $("body").addClass("loading");
        },
        ajaxStop: function () {
            $("body").removeClass("loading");
        }
    });

}

/**
 * Función que nos permite crear los controles necesarios para añadir o modificar 
 * registros, mientras que deshabilita los botones de acciones
 * @returns {Boolean} False para evitar eventos submit
 */
function habilitarAñadirModificar()
{
    id_usuario;

    // Comprobamos si estamos haciendo una insercción o una modificación
    if (this.value.indexOf("Añadir") >= 0)
    {
        // Si es una insercción asignamos los valores del modo
        modo = "A";

        // Eliminamos los valores de los campos imput
        $("div#detalle form input#user").val("");
        $("div#detalle form input#pass").val("");
        $("div#detalle form input#nombre").val("");
    }
    else
    {
        // Si es una modificación asignamos el modo y dejamos el valor de 
        // id_usuario que hemos recogido al cargar la página
        modo = "M";
    }

    // Asignamos atributos y clases para deshabilitar los botones superiores
    $("div#botonera form#añadir > input[type='submit']").attr("disabled", "disabled");
    $("div#botonera form#añadir > input[type='submit']").addClass("deshabilitado");
    $("div#botonera form#modificar > input[type='submit']").attr("disabled", "disabled");
    $("div#botonera form#modificar > input[type='submit']").addClass("deshabilitado");
    $("div#botonera form#eliminar > input[type='submit']").attr("disabled", "disabled");
    $("div#botonera form#eliminar > input[type='submit']").addClass("deshabilitado");

    // Habilitamos los campos para datos
    $("div#detalle form input#user").removeAttr("disabled");
    $("div#detalle form input#pass").removeAttr("disabled");
    $("div#detalle form input#nombre").removeAttr("disabled");


    // Creamos el botón de aceptar
    cadena = "<input tabindex='13' name='boton' id='aceptar' type='submit' value='Aceptar' title='Pulse para confirmar las modificaciones' />";

    // Creamos el botón de cancelar
    cadena += "<input tabindex='14' name='boton' id='cancelar' type='submit' value='Cancelar' title='Pulse para cancelar las modificaciones' />";

    // Creamos dos objetos ocultos para reenviar la información del modo de la página y del identificador del usuario
    cadena += "<input class='oculto' name='id_usuario' type='hidden' value='" + id_usuario + "' />";
    cadena += "<input class='oculto' name='modo' type='hidden' value='" + modo + "' />";

    // Añadimos los botones de aceptar y cancelar al formulario
    $("div#detalle form").append(cadena);

    // Asignamos una función al evento click del botón de cancelar
    $("#detalle form").off("click", "#cancelar").on("click", "#cancelar", cancelarOperacion);

    // Asignamos una función al evento click del botón de aceptar
    $("#detalle form").off("click", "#aceptar").on("click", "#aceptar", aceptarOperacion);

    // Devolvemos falso para que no se envíe el formulario
    return false;
}


/**
 * Función que nos permite deshabilitar los cambios hechos durante una modifición o una addición
 * @returns {Boolean} False para impedir que se lance el evento submit
 */
function cancelarOperacion()
{

    // Comprobamos si estamos en modo modificación o si lo estamos en alta y 
    // el id_usuario es distinto de 0. Esto implica que se ha cancelado la 
    // operación tras pulsar los botones de acción de la pantalla de detalle y 
    // no se está dando un alta desde la pantalla de listado
    if (modo !== "A" || (modo === "A" && id_usuario !== "0"))
    {
        // Recuperamos los valores memoria
        $("div#detalle form input#user").val(usuario);
        $("div#detalle form input#pass").val(contraseña);
        $("div#detalle form input#nombre").val(nombre);

        // Habilitamos los botones
        $("div#botonera form#añadir > input[type='submit']").removeAttr("disabled");
        $("div#botonera form#añadir > input[type='submit']").removeClass("deshabilitado");
        $("div#botonera form#modificar > input[type='submit']").removeAttr("disabled");
        $("div#botonera form#modificar > input[type='submit']").removeClass("deshabilitado");
        $("div#botonera form#eliminar > input[type='submit']").removeAttr("disabled");
        $("div#botonera form#eliminar > input[type='submit']").removeClass("deshabilitado");

        // Deshabilitamos los campos para datos
        $("div#detalle form input#user").attr("disabled", "disabled");
        $("div#detalle form input#pass").attr("disabled", "disabled");
        $("div#detalle form input#nombre").attr("disabled", "disabled");

        // Eliminamos los botones de aceptar, cancelar y los inputs ocultos que 
        // habíamos creado anteriormente
        $("div#detalle form input#aceptar").remove();
        $("div#detalle form input#cancelar").remove();
        $("div#detalle form input[name='id_usuario']").remove();
        $("div#detalle form input[name='modo']").remove();

        $(".error p").replaceWith("<p></p>");
    }
    else
    {
        // Si es un alta iniciada desde el listado, usamos la función navegar y 
        // volvemos al index pasándole como parámetro el índice asignado a 
        // esta página
        navegar(6);
    }

    // Devolvemos false para anular eventos submit 
    return false;
}


/**
 * Función que nos permite validar los datos del formulario
 * @returns {String|salida} Cadena vacia si no hay errores, un mensaje de 
 * error en caso contrario
 */
function validarDatos()
{
    // Inicializamos la variable de salida
    salida = "";

    // Validamos el nombre
    if (!validarCadena($("div#detalle form input#nombre").val()))
    {
        // Si no es válido, modificamos la variable de salida con un mensaje de error
        salida = "Debe introducir un nombre válido";
    }

    // Validamos el usuario
    if (!validarUsuario($("div#detalle form input#user").val()))
    {
        // Si no es válido, modificamos la variable de salida con un mensaje de error
        salida = "Debe introducir un usuario válido";
    }

    // Validamos la contraseña
    if (!validarPass($("div#detalle form input#pass").val()))
    {
        // Si no es válido, modificamos la variable de salida con un mensaje de error
        salida = "Debe introducir una contraseña válida";
    }

    // Devolvemos la salida
    return salida;
}




/**
 * Función que nos permite hacer una petición AJAX para insertar o modificar 
 * un registro
 * @returns {Boolean} False para evitar eventos submit
 */
function aceptarOperacion()
{
    // Realizamos la validación del formulario y volcamos el resultado en 
    // una variable
    resultado = validarDatos();

    // Comprobamos si la validación es correcta
    if (resultado === "")
    {

        // Eliminamos los mensajes de errores que pudiese haber anteriormente
        $(".error p").replaceWith("");


        // Si lo es, hacemos una petición AJAX a la página de mensajes de usuario detalle
        $.ajax({
            // La hacemos por post
            type: "POST",
            // sin cache
            cache: false,
            // Especificamos la url donde se dirigirá la petición
            url: "usuario_detalle_msg.php",
            // Especificamos los datos que pasaremos como parámetros en el post
            data: " user=" + $('div#detalle form input#user').val()
                    + "&pass=" + $('div#detalle form input#pass').val()
                    + "&nombre=" + $('div#detalle form input#nombre').val()
                    + "&modo=" + modo
                    + "&id_usuario=" + id_usuario,
            // Definimos el tipo de datos que se nos va a devolver
            dataType: "json",
            // Definimos que hacer en caso de petición exitosa
            success: function (data) {

                // Asignamos los valores recuperados del usuario y los asignamos 
                // las variables 
                id_usuario = data.id_usuario;
                usuario = data.user;
                nombre = data.nombre;
                contraseña = data.pass;


                // Llamamos a la función cancelarOperación para que deshabilite el 
                // formulario, limpie mensajes de error, y asigne el valor de las 
                // variables a sus campos input correspondientes
                cancelarOperacion();
            },
            // Definimos que hacer en caso de error
            error: function (jqXHR, textStatus, errorThrown) {

                // Creamos una cadena con el mensaje de respuesta
                var cadena = "<p>" + jqXHR.responseText + "</p>";

                // Lo ponemos en el div para mensajes de error
                $(".error p").replaceWith(cadena);
            }
        });
    }
    else
    {
        // Si no, creamos una cadena con el resultado de la validación
        var cadena = "<p>" + resultado + "</p>";

        // Lo ponemos en el div para mensajes de error
        $(".error p").replaceWith(cadena);
    }


    // Devolvemos false para anular eventos submit 
    return false;
}

/**
 * Función que nos permite eliminar un registro de usuario
 * @returns {undefined}
 */
function eliminarRegistro()
{
    
    if (confirm("¿Realmente desea borrar el registro?"))
    {
        // Pedimos confirmación al usuario pare realizar el borrado
        // Si lo es, hacemos una petición AJAX a la página de mensajes de usuario detalle
        $.ajax({
            // La hacemos por post
            type: "POST",
            // sin cache
            cache: false,
            // Especificamos la url donde se dirigirá la petición
            url: "usuario_detalle_msg.php",
            // Especificamos los datos que pasaremos como parámetros en el post
            data: " modo=E"
                    + "&id_usuario=" + id_usuario,
            // Definimos el tipo de datos que se nos va a devolver
            dataType: "json",
            // Definimos que hacer en caso de petición exitosa
            success: function () {

                // Tras el borrado, navegamos de nuevo a la página inicial
                navegar("6");
            },
            // Definimos que hacer en caso de error
            error: function (jqXHR, textStatus, errorThrown) {

                // Creamos una cadena con el mensaje de respuesta
                var cadena = "<p>" + jqXHR.responseText + "</p>";

                // Lo ponemos en el div para mensajes de error
                $(".error p").replaceWith(cadena);

                // Devolvemos false para que no se ejecuten eventos submit
                return false;
            }
        });
    }
    else
    {
        // Devolvemos false para que no se ejecuten eventos submit
        return false;

    }
}