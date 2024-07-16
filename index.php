<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataFile = 'data.json';
    $data = json_decode(file_get_contents($dataFile), true);

    if (isset($_POST['data'])) {
        // Update data
        $data = json_decode($_POST['data'], true);
    } else {
        // Add new data
        $newData = [
            'productName' => $_POST['productName'],
            'quantity' => $_POST['quantity'],
            'price' => $_POST['price'],
            'datetime' => date('Y-m-d H:i:s')
        ];
        $data[] = $newData;
    }

    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
    echo json_encode(['status' => 'success']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Form</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Add any custom styles here */
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2>Product Form</h2>
        <form id="productForm">
            <div class="form-group">
                <label for="productName">Product Name:</label>
                <input type="text" class="form-control" id="productName" name="productName" required>
            </div>
            <div class="form-group">
                <label for="quantity">Quantity in Stock:</label>
                <input type="number" class="form-control" id="quantity" name="quantity" required>
            </div>
            <div class="form-group">
                <label for="price">Price per Item:</label>
                <input type="number" class="form-control" id="price" name="price" step="0.01" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
        <h2 class="mt-5">Submitted Data</h2>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity in Stock</th>
                    <th>Price per Item</th>
                    <th>Datetime Submitted</th>
                    <th>Total Value</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="dataTable">
                <!-- Data will be appended here -->
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4">Sum Total</td>
                    <td id="sumTotal"></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Load data on page load
            loadData();

            // Handle form submission
            $('#productForm').on('submit', function(e) {
                e.preventDefault();
                const formData = {
                    productName: $('#productName').val(),
                    quantity: $('#quantity').val(),
                    price: $('#price').val()
                };
                $.ajax({
                    type: 'POST',
                    url: 'index.php',
                    data: formData,
                    success: function(response) {
                        loadData();
                        $('#productForm')[0].reset();
                    }
                });
            });

            // Load data function
            function loadData() {
                $.getJSON('data.json', function(data) {
                    let rows = '';
                    let sumTotal = 0;
                    data.forEach((item, index) => {
                        const totalValue = item.quantity * item.price;
                        sumTotal += totalValue;
                        rows += `
                            <tr>
                                <td>${item.productName}</td>
                                <td>${item.quantity}</td>
                                <td>${item.price}</td>
                                <td>${item.datetime}</td>
                                <td>${totalValue.toFixed(2)}</td>
                                <td>
                                    <button class="btn btn-warning btn-sm edit-btn" data-index="${index}">Edit</button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#dataTable').html(rows);
                    $('#sumTotal').text(sumTotal.toFixed(2));
                });
            }

            // Handle edit button click
            $('table').on('click', '.edit-btn', function() {
                const index = $(this).data('index');
                $.getJSON('data.json', function(data) {
                    const item = data[index];
                    $('#productName').val(item.productName);
                    $('#quantity').val(item.quantity);
                    $('#price').val(item.price);
                    $('#productForm').off('submit').on('submit', function(e) {
                        e.preventDefault();
                        const updatedData = {
                            productName: $('#productName').val(),
                            quantity: $('#quantity').val(),
                            price: $('#price').val(),
                            datetime: item.datetime
                        };
                        data[index] = updatedData;
                        $.ajax({
                            type: 'POST',
                            url: 'index.php',
                            data: {
                                data: JSON.stringify(data)
                            },
                            success: function(response) {
                                loadData();
                                $('#productForm')[0].reset();
                                $('#productForm').off('submit').on('submit', function(e) {
                                    e.preventDefault();
                                    const formData = {
                                        productName: $('#productName').val(),
                                        quantity: $('#quantity').val(),
                                        price: $('#price').val()
                                    };
                                    $.ajax({
                                        type: 'POST',
                                        url: 'index.php',
                                        data: formData,
                                        success: function(response) {
                                            loadData();
                                            $('#productForm')[0].reset();
                                        }
                                    });
                                });
                            }
                        });
                    });
                });
            });
        });
    </script>
</body>

</html>