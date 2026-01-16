<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema IMC</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form {
            margin-bottom: 30px;
        }
        .form_label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        .form_input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .imc-low { background-color: #ffeb3b; }
        .imc-normal { background-color: #8bc34a; }
        .imc-overweight { background-color: #ff9800; }
        .imc-obesity { background-color: #f44336; color: white; }
        .btn-modify { background-color: #2196F3; }
        .btn-delete { background-color: #f44336; }
        .btn-back { background-color: #607D8B; }
        .actions a {
            padding: 5px 10px;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 5px;
            display: inline-block;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        .alert-error {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        // Configuración de conexión a la base de datos
        $conexion = mysqli_connect("localhost", "root", "", "indicemasa");
        if (!$conexion) {
            echo "<div class='alert alert-error'>Error al conectarse a la base de datos</div>";
        }

        // Procesar diferentes acciones según los parámetros
        $action = isset($_GET['action']) ? $_GET['action'] : '';

        // Mostrar formulario principal por defecto
        if ($action == '' || $action == 'form') {
            echo '
            <form action="?action=save" method="POST" class="form">
                <h1>Datos de la persona</h1>
                <label class="form_label">Id</label>
                <input class="form_input" type="text" name="id" required>
                <label class="form_label">Nombre</label>
                <input class="form_input" type="text" name="nombre" required>
                <label class="form_label">Peso (kg)</label>
                <input class="form_input" type="number" step="0.01" name="peso" required>
                <label class="form_label">Altura (m)</label>
                <input class="form_input" type="number" step="0.01" name="altura" required>
                <button type="submit">GUARDAR</button>
                <button type="button" onclick="window.location.href=\'?action=show\'">MOSTRAR DATOS</button>
            </form>';
        }

        // Guardar datos
        elseif ($action == 'save' && $_SERVER["REQUEST_METHOD"] == "POST") {
            $id = mysqli_real_escape_string($conexion, $_POST['id']);
            $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
            $peso = mysqli_real_escape_string($conexion, $_POST['peso']);
            $altura = mysqli_real_escape_string($conexion, $_POST['altura']);

            $data = "INSERT INTO imc(id, nombre, peso, altura) 
                      VALUES ('$id', '$nombre', '$peso', '$altura')";
            $ejecutar = mysqli_query($conexion, $data);

            if ($ejecutar) {
                echo "<div class='alert alert-success'><h2>Datos insertados correctamente</h2></div>";
                echo "<script>alert('Datos guardados correctamente');</script>";
            } else {
                echo "<div class='alert alert-error'><h2>Error: los datos no pudieron guardarse</h2></div>";
                echo "<script>alert('Error al guardar los datos');</script>";
            }
            
            echo '<br><button onclick="window.location.href=\'?action=form\'">Regresar</button>';
        }

        // Mostrar datos
        elseif ($action == 'show') {
            echo '<h1>Lista de Personas</h1>';
            
            echo '<div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Nombre</th>
                            <th>Peso</th>
                            <th>Altura</th>
                            <th>IMC</th>
                            <th>Estado</th>
                            <th>Consejo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            $sql = "SELECT * FROM imc";
            $resultado = mysqli_query($conexion, $sql);
            
            if ($resultado && mysqli_num_rows($resultado) > 0) {
                while ($fila = mysqli_fetch_assoc($resultado)) {
                    $peso = $fila['peso'];
                    $altura = $fila['altura'];
                    $imc = $peso / ($altura * $altura);
                    $estado = "";
                    $consejo = "";
                    $imcClass = "";
                    
                    if ($imc < 18.5) {
                        $estado = "Bajo peso";
                        $consejo = "Debes aumentar de peso. Consulta a un nutricionista.";
                        $imcClass = "imc-low";
                    } elseif ($imc >= 18.5 && $imc < 25) {
                        $estado = "Peso normal";
                        $consejo = "¡Excelente! Mantén tus hábitos saludables.";
                        $imcClass = "imc-normal";
                    } elseif ($imc >= 25 && $imc < 30) {
                        $estado = "Sobrepeso";
                        $consejo = "Necesitas hacer ejercicio y mejorar tu alimentación.";
                        $imcClass = "imc-overweight";
                    } else {
                        $estado = "Obesidad";
                        $consejo = "Consulta a un médico y nutricionista urgentemente.";
                        $imcClass = "imc-obesity";
                    }
                    
                    echo "<tr>";
                    echo "<td>".$fila['id']."</td>";
                    echo "<td>".$fila['nombre']."</td>";
                    echo "<td>".$fila['peso']." kg</td>";
                    echo "<td>".$fila['altura']." m</td>";
                    echo "<td class='".$imcClass."'>".number_format($imc, 2)."</td>";
                    echo "<td class='".$imcClass."'>".$estado."</td>";
                    echo "<td>".$consejo."</td>";
                    echo "<td>
                            <div class='actions'>
                                <a class='btn-modify' href='?action=modify&id=".$fila['id']."'>Modificar</a>
                                <a class='btn-delete' href='?action=delete&id=".$fila['id']."' onclick=\"return confirm('¿Seguro que deseas eliminar a esta persona?');\">Eliminar</a>
                            </div>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8' style='text-align:center;'>No hay datos registrados</td></tr>";
            }
            
            echo '</tbody>
                </table>
            </div>
            
            <div class="footer">
                <button class="btn-back" onclick="window.location.href=\'?action=form\'">Regresar al Formulario</button>
            </div>';
        }

        // Modificar datos - formulario
        elseif ($action == 'modify' && isset($_GET['id'])) {
            $id = $_GET['id'];
            $sql = "SELECT * FROM imc WHERE id='$id'";
            $resultado = mysqli_query($conexion, $sql);
            
            if ($resultado && mysqli_num_rows($resultado) > 0) {
                $fila = mysqli_fetch_assoc($resultado);
                
                echo '<h1>Modificar datos</h1>
                <form action="?action=update" method="POST">
                    <input class="form_input" type="hidden" name="id" value="'.$fila['id'].'">
                    <label class="form_label">Nombre:</label>
                    <input class="form_input" type="text" name="nombre" value="'.$fila['nombre'].'" required><br><br>
                    <label class="form_label">Peso (kg):</label>
                    <input class="form_input" type="number" step="0.01" name="peso" value="'.$fila['peso'].'" required><br><br>
                    <label class="form_label">Altura (m):</label>
                    <input class="form_input" type="number" step="0.01" name="altura" value="'.$fila['altura'].'" required><br><br>

                    <button type="submit">Guardar cambios</button>
                    <button type="button" onclick="window.location.href=\'?action=show\'">Cancelar</button>
                </form>';
            } else {
                echo "<div class='alert alert-error'>No se encontró el registro</div>";
                echo '<button onclick="window.location.href=\'?action=show\'">Regresar</button>';
            }
        }

        // Actualizar datos
        elseif ($action == 'update' && $_SERVER["REQUEST_METHOD"] == "POST") {
            $id = mysqli_real_escape_string($conexion, $_POST['id']);
            $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
            $peso = mysqli_real_escape_string($conexion, $_POST['peso']);
            $altura = mysqli_real_escape_string($conexion, $_POST['altura']);
            
            $sql = "UPDATE imc 
                    SET nombre='$nombre', peso='$peso', altura='$altura'
                    WHERE id='$id'";

            if (mysqli_query($conexion, $sql)) {
                echo "<script>alert('Datos actualizados correctamente');window.location='?action=show';</script>";
            } else {
                echo "<div class='alert alert-error'>Error al actualizar: " . mysqli_error($conexion) . "</div>";
                echo '<button onclick="window.location.href=\'?action=show\'">Regresar</button>';
            }
        }

        // Eliminar datos
        elseif ($action == 'delete' && isset($_GET['id'])) {
            $id = $_GET['id'];
            $sql = "DELETE FROM imc WHERE id='$id'";

            if (mysqli_query($conexion, $sql)) {
                echo "<script>alert('Registro eliminado correctamente');window.location='?action=show';</script>";
            } else {
                echo "<div class='alert alert-error'>Error al eliminar: " . mysqli_error($conexion) . "</div>";
                echo '<button onclick="window.location.href=\'?action=show\'">Regresar</button>';
            }
        }

        // Cerrar conexión
        if ($conexion) {
            mysqli_close($conexion);
        }
        ?>
    </div>
</body>
</html>