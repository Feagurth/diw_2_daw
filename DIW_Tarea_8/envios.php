<?php
require_once './objetos/Envio.php';

try {

    // Recuperamos los valores de los filtros
    $filtro = (isset($_POST['filtro']) ? $_POST['filtro'] : "");
    $tipoFiltro = (isset($_POST['tipoFiltro']) ? $_POST['tipoFiltro'] : "1");

    // Creamos un nuevo objeto de acceso a base de datos
    $db = new DB();

    // Limpiamos el valor de id_envio de la sesión, dejando así la pantalla 
    // de detalles lista para volver a usar
    unset($_SESSION['id_envio']);

    // Obtenemos el listado de envios pasándole los valores de filtro
    $envios = $db->listarEnvios($filtro, $tipoFiltro);
} catch (Exception $ex) {
    $error = $ex->getMessage();
}
?>
<div class="listado">
    <div id="botonera">
        <h3>Listado de envíos</h3>
        <form id="nuevo" action='envio_detalle.php' method='post' >
            <input type='submit' tabindex="8" value='Nuevo Envio' alt='Nuevo Envio' title="Pulse el botón para crear un nuevo envio" />
            <input class='oculto' name='id_envio' type='text' value='0' />
            <input class='oculto' name='modo' type='text' value='A' />
        </form>
        <form id="filtro" action='index.php' method='post' >
            <input type='submit' tabindex="11" value='Filtrar resultados' alt='Filtrar resultados' title="Pulse el botón para filtrar los resultados"/>            
            <select name="tipoFiltro" tabindex="10" title="Seleccione el tipo de filtro">
                <option <?php if ($tipoFiltro === "1") echo "selected=\"selected\" " ?> value="1">Fecha</option>
                <option <?php if ($tipoFiltro === "2") echo "selected=\"selected\" " ?> value="2">Cuenta de E-Mail</option>
                <option <?php if ($tipoFiltro === "3") echo "selected=\"selected\" " ?> value="3">Nombre de Empleado</option>
                <option <?php if ($tipoFiltro === "4") echo "selected=\"selected\" " ?> value="4">Apellido de Empleado</option>
                <option <?php if ($tipoFiltro === "5") echo "selected=\"selected\" " ?> value="5">E-Mail de Empleado</option>
                <option <?php if ($tipoFiltro === "6") echo "selected=\"selected\" " ?> value="6">Nombre de Fichero</option>
                <option <?php if ($tipoFiltro === "7") echo "selected=\"selected\" " ?> value="7">Descripción de Fichero</option>
            </select>
            <input id="textoFiltro" tabindex="9" type="text" alt="Introduzca un texto para filtrar los resultados" maxlength="30" title="Introduzca la cadena por la que filtrar los resultados" name="filtro"  value="<?php echo $filtro ?>" />
            <input class='oculto' name='indice' type='text' value='4' />
        </form>
    </div>
    <div class="error">
        <p><?php echo $error ?></p>
    </div>
    <table>
        <thead>
            <tr>
                <td>Fecha</td>    
                <td>Cuenta de E-Mail</td>    
                <td>Nombre de Empleado</td>    
                <td>Apellido de Empleado</td>    
                <td>E-Mail de Empleado</td>    
                <td>Nombre de Fichero</td>    
                <td>Descripción de Fichero</td>                    
                <td>Detalles</td>    
            </tr>        
        </thead>    
        <tbody>
            <?php
            // Verificamos si tenemos algún tipo de error
            if ($error === "") {
                // Inicializamos un contador para asignar los estilos a cada linea
                $i = 0;

                // Recorremos cada uno de los registros que hemos recuperado 
                // mediante la consulta a la base de datos
                foreach ($envios as $envio) {

                    // Si el contador es un número par, le daremos un estilo y si 
                    // es impar le daremos otro
                    if ($i % 2 === 0) {
                        echo '<tr class="pijama1">';
                    } else {
                        echo '<tr class="pijama2">';
                    }

                    // Imprimimos celda con los valores recuperados de cada objeto 
                    // envio que hay en los registros recuperados
                    echo '<td title="' . $envio->getFecha_envio() . '">' . textoElipsis($envio->getFecha_envio(), 30) . '</td>';
                    echo '<td title="' . $envio->getEmail_envio() . '">' . textoElipsis($envio->getEmail_envio(), 50) . '</td>';
                    echo '<td title="' . $envio->getNombre_empleado() . '">' . textoElipsis($envio->getNombre_empleado(), 30) . '</td>';
                    echo '<td title="' . $envio->getApellido_empleado() . '">' . textoElipsis($envio->getApellido_empleado(), 30) . '</td>';
                    echo '<td title="' . $envio->getEmail_empleado() . '">' . textoElipsis($envio->getEmail_empleado(), 30) . '</td>';
                    echo '<td title="' . $envio->getNombre_fichero() . '">' . textoElipsis($envio->getNombre_fichero(), 50) . '</td>';
                    echo '<td title="' . $envio->getDescripcion_fichero() . '">' . textoElipsis($envio->getDescripcion_fichero(), 50) . '</td>';

                    // Añadimos una última fila con un botón con imagen para 
                    // acceder a los detalles del envio.
                    echo '<td>';
                    echo "<form action='envio_detalle.php' method='post' >";
                    echo "<button name='button' value='Detalles' alt='Detalles'><img src='imagenes/details.png' alt='Ver Detalles' title='Pulse para ver los detalles' /></button>";
                    echo "<input class='oculto' name='id_envio' type='text' value='" . $envio->getId_envio() . "' />";
                    echo "<input class='oculto' name='modo' type='text' value='V' />";
                    echo "</form>";
                    echo '</td>';
                    echo '</tr>';

                    // Incrementamos el contador
                    $i++;
                }
            }
            ?>
        </tbody>
    </table>
</div>