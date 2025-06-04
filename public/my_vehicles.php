<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Veículos - Sistema de Lavagem</title>
    <style>
        /* Estilos Mobile First - Adaptar de login/register.php */
        body {
            font-family: sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 700px; /* Maior para lista e form */
            margin-top: 30px;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        button {
            padding: 10px 15px;
            background-color: #3498db; /* Azul */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.3s;
            margin-top: 10px;
        }
        button:hover {
            background-color: #2980b9;
        }
        button.delete-btn {
            background-color: #e74c3c; /* Vermelho */
            font-size: 12px;
            padding: 5px 10px;
            margin-top: 0;
        }
        button.delete-btn:hover {
            background-color: #c0392b;
        }
        .add-vehicle-form {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .vehicle-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .vehicle-list li {
            background-color: #f9f9f9;
            border: 1px solid #eee;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap; /* Para mobile */
        }
         .vehicle-list li span {
            flex-basis: 100%; /* Mobile: info ocupa linha toda */
            margin-bottom: 10px; /* Espaço antes do botão */
            font-size: 15px;
            color: #333;
        }
        .vehicle-list li button {
             flex-shrink: 0; /* Não encolher o botão */
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Adaptações para telas maiores */
        @media (min-width: 600px) {
            .container {
                padding: 40px;
            }
             .vehicle-list li span {
                flex-basis: auto; /* Desktop: info e botão na mesma linha */
                margin-bottom: 0;
            }
            .add-vehicle-form {
                display: flex;
                gap: 15px;
                align-items: flex-end; /* Alinha botão com inputs */
            }
            .add-vehicle-form div { flex-grow: 1; }
            .add-vehicle-form button { width: auto; margin-top: 0; }
            input[type="text"],
            input[type="number"] {
                 margin-bottom: 0; /* Remove margem inferior no desktop */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Meus Veículos</h2>

        <div id="message-area"></div>

        <h3>Veículos Cadastrados</h3>
        <ul id="vehicle-list" class="vehicle-list">
            <!-- Veículos serão carregados aqui via JS -->
            <li>Carregando veículos...</li>
        </ul>

        <div class="add-vehicle-form">
            <h3>Adicionar Novo Veículo</h3>
            <form id="add-vehicle-form">
                <div>
                    <label for="model">Modelo:</label>
                    <input type="text" id="model" name="model" required>
                </div>
                <div>
                    <label for="year">Ano:</label>
                    <input type="number" id="year" name="year" min="1900" max="<?php echo date(
'Y
') + 1; ?>" required>
                </div>
                 <div>
                    <label for="color">Cor:</label>
                    <input type="text" id="color" name="color" required>
                </div>
                 <div>
                    <label for="plate">Placa:</label>
                    <input type="text" id="plate" name="plate" required>
                </div>
                <button type="submit">Adicionar Veículo</button>
            </form>
        </div>
         <p style="text-align: center; margin-top: 30px;"><a href="/">Voltar para o início</a></p> <!-- Link de volta -->
    </div>

    <script>
        const vehicleList = document.getElementById(
'vehicle-list
');
        const addVehicleForm = document.getElementById(
'add-vehicle-form
');
        const messageArea = document.getElementById(
'message-area
');

        // Função para exibir mensagens
        function showMessage(text, type = 
'error
') {
            messageArea.innerHTML = `<div class="message ${type}">${text}</div>`;
        }

        // Função para carregar veículos
        async function loadVehicles() {
            vehicleList.innerHTML = 
'<li>Carregando veículos...</li>
';
            try {
                const response = await fetch(
'/api/users/me/vehicles
', {
                    method: 
'GET
',
                    headers: { 
'Accept
': 
'application/json
' }
                });
                const result = await response.json();

                if (response.ok && result.success) {
                    vehicleList.innerHTML = 
''
; // Limpa a lista
                    if (result.vehicles && result.vehicles.length > 0) {
                        result.vehicles.forEach(vehicle => {
                            const li = document.createElement(
'li
');
                            li.innerHTML = `
                                <span>
                                    <strong>Modelo:</strong> ${vehicle.model} | 
                                    <strong>Ano:</strong> ${vehicle.year} | 
                                    <strong>Cor:</strong> ${vehicle.color} | 
                                    <strong>Placa:</strong> ${vehicle.plate}
                                </span>
                                <button class="delete-btn" data-id="${vehicle.id}">Excluir</button>
                            `;
                            vehicleList.appendChild(li);
                        });
                    } else {
                        vehicleList.innerHTML = 
'<li>Nenhum veículo cadastrado.</li>
';
                    }
                } else {
                    showMessage(result.message || 
'Erro ao carregar veículos.
');
                    vehicleList.innerHTML = 
'<li>Erro ao carregar.</li>
';
                }
            } catch (error) {
                console.error(
'Erro ao buscar veículos:
', error);
                showMessage(
'Erro de comunicação ao buscar veículos.
');
                 vehicleList.innerHTML = 
'<li>Erro de comunicação.</li>
';
            }
        }

        // Event listener para adicionar veículo
        addVehicleForm.addEventListener(
'submit
', async (event) => {
            event.preventDefault();
            messageArea.innerHTML = 
''
;
            const formData = new FormData(addVehicleForm);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(
'/api/users/me/vehicles
', {
                    method: 
'POST
',
                    headers: {
                        
'Content-Type
': 
'application/json
',
                        
'Accept
': 
'application/json
'
                    },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (response.ok && result.success) {
                    showMessage(result.message || 
'Veículo adicionado com sucesso!
', 
'success
');
                    addVehicleForm.reset(); // Limpa o formulário
                    loadVehicles(); // Recarrega a lista
                } else {
                    showMessage(result.message || 
'Erro ao adicionar veículo.
');
                }
            } catch (error) {
                 console.error(
'Erro ao adicionar veículo:
', error);
                showMessage(
'Erro de comunicação ao adicionar veículo.
');
            }
        });

        // Event listener para excluir veículo (delegação de evento)
        vehicleList.addEventListener(
'click
', async (event) => {
            if (event.target.classList.contains(
'delete-btn
')) {
                const vehicleId = event.target.dataset.id;
                if (!vehicleId || !confirm(
'Tem certeza que deseja excluir este veículo?
')) {
                    return;
                }
                messageArea.innerHTML = 
''
;

                try {
                     const response = await fetch(`/api/users/me/vehicles/${vehicleId}`, {
                        method: 
'DELETE
',
                        headers: { 
'Accept
': 
'application/json
' }
                    });
                    const result = await response.json();

                    if (response.ok && result.success) {
                        showMessage(result.message || 
'Veículo excluído com sucesso!
', 
'success
');
                        loadVehicles(); // Recarrega a lista
                    } else {
                        showMessage(result.message || 
'Erro ao excluir veículo.
');
                    }
                } catch (error) {
                    console.error(
'Erro ao excluir veículo:
', error);
                    showMessage(
'Erro de comunicação ao excluir veículo.
');
                }
            }
        });

        // Carrega os veículos ao iniciar a página
        loadVehicles();

    </script>

</body>
</html>

