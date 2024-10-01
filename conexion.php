<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phone Book UWU</title>

    <style>
        body {
            margin: 20px;
            background-color: rgb(241, 238, 238);
        }

        #encabezado {
            background-color: #ce6a6a;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            text-align: center;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 30px;
        }

        #areaBusqueda{
            display: flex;
            flex-direction: row;
        }

        form {
            max-width: 400px;
            margin: auto;
        }

        label {
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
            font-size: 15px;
            display: block;
            margin: 8px 0px 5px;
            background-color: rgb(216, 184, 184);
            border-radius: 6px;
            padding: 5px;
        }

        input,
        select {
            width: 100%;
            padding: 8px;
            margin-left: 5px;
            margin-right: 5px;
            margin-bottom: 10px;
            border-radius: 6px;
            background-color: rgb(224, 224, 224);
            border-color: transparent;
        }

        #areaEnvio {
            text-align: center;
            margin: 10px;
        }

        button {
            padding: 10px;
            background-color: #4CAF50;
            border-radius: 6px;
            color: white;
            border: none;
            cursor: pointer;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            font-size: 13px;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <?php
    // Conectar a la base de datos
    $conn = new mysqli("localhost", "root", "123456", "phonebookdb");

    // Verificar conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Verificar si los datos han sido enviados para guardado
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Save'])) {
        $Name = ($_POST['Name']);
        $LastName = ($_POST['LastName']);
        $Birthday = ($_POST['Birthday']);
        $Code = ($_POST['Code']);
        $Lada = ($_POST['Lada']);
        $Number = ($_POST['Number']);
        $PhoneType = ($_POST['PhoneType']);
        $Mail = ($_POST['Mail']);
        $MailType = ($_POST['MailType']);
        $Address = ($_POST['Address']);
        $ZipCode = ($_POST['ZipCode']);
        $AddressType = ($_POST['AddressType']);

        // Iniciar transacción
        $conn->begin_transaction();

        try {
            // Insertar datos en la tabla "contact"
            $sqlContact = "INSERT INTO contact (Name, LastName, Birthday) VALUES ('$Name', '$LastName', '$Birthday')";
            if ($conn->query($sqlContact) === FALSE) {
                throw new Exception("Error al insertar en 'contact': " . $conn->error);
            }

            // Obtener el ID del último registro insertado en "contact"
            $ID_Contact = $conn->insert_id;

            // Insertar datos en la tabla "telephone"
            $sqlTelephone = "INSERT INTO telephone (ID_Contact, Code, Lada, Number, PhoneType) 
                             VALUES ('$ID_Contact', '$Code', '$Lada', '$Number', '$PhoneType')";
            if ($conn->query($sqlTelephone) === FALSE) {
                throw new Exception("Error al insertar en 'telephone': " . $conn->error);
            }

            $sqlMail = "INSERT INTO mail (ID_Contact, Mail, MailType) 
                             VALUES ('$ID_Contact', '$Mail', '$MailType')";
            if ($conn->query($sqlMail) === FALSE) {
                throw new Exception("Error al insertar en 'mail': " . $conn->error);
            }

            $sqlAddress = "INSERT INTO address (ID_Contact, Address, ZipCode, AddressType) 
                             VALUES ('$ID_Contact', '$Address', '$ZipCode', '$AddressType')";
            if ($conn->query($sqlAddress) === FALSE) {
                throw new Exception("Error al insertar en 'address': " . $conn->error);
            }

            // Confirmar transacción
            $conn->commit();
            echo "Registro guardado exitosamente.";
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }

        // Cerrar la conexión después de la consulta
        $conn->close();
    }

    // Verificar si se ha enviado una solicitud de búsqueda
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Search'])) {
        $searchName = $_POST['searchName'];
        $searchLastName = $_POST['searchLastName'];

        // Realizar la consulta de búsqueda
        $sqlSearch = "SELECT contact.ID_Contact, contact.Name, contact.LastName, telephone.Code, telephone.Lada, telephone.Number, telephone.PhoneType, 
                            mail.Mail, mail.MailType, address.Address, address.ZipCode, address.AddressType
                      FROM contact 
                      LEFT JOIN telephone ON contact.ID_Contact = telephone.ID_Contact
                      LEFT JOIN mail ON contact.ID_Contact = mail.ID_Contact
                      LEFT JOIN address ON contact.ID_Contact = address.ID_Contact 
                      WHERE contact.Name LIKE '%$searchName%' AND contact.LastName LIKE '%$searchLastName%'";

        $result = $conn->query($sqlSearch);

        if ($result->num_rows > 0) {
            echo "<h3>Resultados de la búsqueda:</h3>";
            echo "<table border='1'>
                    <tr>
                        <th>Name</th>
                        <th>Last Name</th>
                        <th>Code</th>
                        <th>Lada</th>
                        <th>Phone Number</th>
                        <th>Phone Type</th>
                        <th>Email</th>
                        <th>Mail Type</th>
                        <th>Address</th>
                        <th>Zip Code</th>
                        <th>Address Type</th>
                    </tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . $row['Name'] . "</td>
                        <td>" . $row['LastName'] . "</td>
                        <td>" . $row['Code'] . "</td>
                        <td>" . $row['Lada'] . "</td>
                        <td>" . $row['Number'] . "</td>
                         <td>" . $row['PhoneType'] . "</td>
                        <td>" . $row['Mail'] . "</td>
                        <td>" . $row['MailType'] . "</td>
                        <td>" . $row['Address'] . "</td>
                        <td>" . $row['ZipCode'] . "</td>
                        <td>" . $row['AddressType'] . "</td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "No se encontraron resultados.";
        }
    }
    ?>

    <h3 id="encabezado">Phone Book</h3>

    <!-- Formulario de búsqueda -->
    <form action="" method="post">
        <h3>Search contact</h3>
        <div id="areaBusqueda">
            <label for="searchName">NAMES:</label>
            <input type="text" id="searchName" name="searchName" placeholder="Buscar por nombre">

            <label for="searchLastName">LAST NAME:</label>
            <input type="text" id="searchLastName" name="searchLastName" placeholder="Buscar por apellido">
        </div>

        <div id="areaEnvio">
            <button type="submit" name="Search" value="Search">Buscar</button>
        </div>
    </form>

    <!-- Formulario de guardado -->
    <form action="" method="post">
        <h3>Add contact</h3>

        <label for="Name">NAME:</label>
        <input type="text" id="Name" name="Name" placeholder="Ingrese su nombre" required>

        <label for="LastName">LAST NAME:</label>
        <input type="text" id="LastName" name="LastName" placeholder="Ingrese su apellido" required>

        <label for="Birthday">BIRTHDAY:</label>
        <input type="date" id="Birthday" name="Birthday" required>

        <label for="Code">CODE:</label>
        <input type="tel" id="Code" name="Code" placeholder="Ingrese código de su país" required>

        <label for="Lada">LADA:</label>
        <input type="tel" id="Lada" name="Lada" placeholder="Ingrese LADA de su país" required>

        <label for="Number">PHONE NUMBER:</label>
        <input type="tel" id="Number" name="Number" placeholder="Ingrese su número" required>

        <label for="PhoneType">PHONE TYPE:</label>
        <select id="PhoneType" name="PhoneType">
            <option value="Movil">Movil</option>
            <option value="Trabajo">Trabajo</option>
            <option value="Casa">Casa</option>
        </select>

        <label for="Mail">EMAIL:</label>
        <input type="Mail" id="Mail" name="Mail" placeholder="Ingrese su correo" required>

        <label for="MailType">EMAIL TYPE:</label>
        <select id="MailType" name="MailType">
            <option value="Personal">Personal</option>
            <option value="Trabajo">Trabajo</option>
        </select>

        <label for="Address">ADDRESS:</label>
        <input type="text" id="Address" name="Address" placeholder="Ingrese su dirección" required>

        <label for="AddressType">ADDRESS TYPE:</label>
        <select id="AddressType" name="AddressType">
            <option value="Personal">Personal</option>
            <option value="Trabajo">Trabajo</option>
        </select>

        <label for="ZipCode">ZIP CODE:</label>
        <input type="text" id="ZipCode" name="ZipCode" placeholder="Ingresa código postal" required>

        <div id="areaEnvio">
            <button type="submit" name="Save" value="Submit">Save</button>
        </div>
    </form>
</body>

</html>