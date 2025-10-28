<?php
require 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sales</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="p-3">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Sales</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSaleModal">
      + Add Sale
    </button>
  </div>

  <!-- Table of previous sales -->
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>ID</th>
        <th>Date</th>
        <th>Customer</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $sql = "SELECT s.id, s.sale_date, s.total_amount, c.name AS customer_name
              FROM sales s
              LEFT JOIN customers c ON s.customer_id = c.id
              ORDER BY s.sale_date DESC";
      $res = $conn->query($sql);
      while($row = $res->fetch_assoc()):
      ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['sale_date'] ?></td>
        <td><?= htmlspecialchars($row['customer_name']) ?></td>
        <td><?= $row['total_amount'] ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <!-- Add Sale Modal -->
  <div class="modal fade" id="addSaleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="saleForm">
          <div class="modal-header">
            <h5 class="modal-title">Add Sale</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <div class="mb-3">
              <label for="customerSelect" class="form-label">Select Customer</label>
              <div class="input-group">
                <select id="customerSelect" name="customer_id" class="form-select" required></select>
                <button type="button" class="btn btn-outline-secondary" id="addCustomerBtn">+ New</button>
              </div>
            </div>

            <table class="table" id="saleItemsTable">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Qty</th>
                  <th>Price</th>
                  <th>Subtotal</th>
                  <th></th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>

            <button type="button" class="btn btn-secondary btn-sm" id="addRowBtn">+ Add Product</button>

            <div class="mt-3 text-end">
              <h5>Total: ₹<span id="grandTotal">0.00</span></h5>
            </div>

            <div class="mb-3">
              <label for="notes" class="form-label">Notes</label>
              <textarea name="notes" id="notes" class="form-control"></textarea>
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Save Sale</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Customer Modal -->
  <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="customerForm">
          <div class="modal-header">
            <h5 class="modal-title">Add New Customer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="customerName" class="form-label">Name *</label>
              <input type="text" id="customerName" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="customerEmail" class="form-label">Email</label>
              <input type="email" id="customerEmail" name="email" class="form-control">
            </div>
            <div class="mb-3">
              <label for="customerPhone" class="form-label">Phone</label>
              <input type="text" id="customerPhone" name="phone" class="form-control">
            </div>
            <div class="mb-3">
              <label for="customerAddress" class="form-label">Address</label>
              <textarea id="customerAddress" name="address" class="form-control" rows="2"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Customer</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  let products = [];
  let addCustomerModal, addSaleModal;

  $(document).ready(function() {
    addCustomerModal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
    addSaleModal = new bootstrap.Modal(document.getElementById('addSaleModal'));
  });

  // Load customers & products when modal opens
  $('#addSaleModal').on('show.bs.modal', function() {
    loadCustomers();

    // Load products (once)
    if (products.length === 0) {
      $.getJSON('get_products.php', function(data) {
        products = data;
      });
    }
  });

  function loadCustomers() {
    $.getJSON('get_customers.php', function(data) {
      let html = '<option value="">-- Select Customer --</option>';
      data.forEach(c => html += `<option value="${c.id}">${c.name}</option>`);
      $('#customerSelect').html(html);
    });
  }

  // Open Add Customer Modal
  $('#addCustomerBtn').click(function() {
    addCustomerModal.show();
  });

  // Submit new customer form
  $('#customerForm').submit(function(e) {
    e.preventDefault();
    $.ajax({
      url: 'add_customer.php',
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.status === 'success') {
          alert('Customer added successfully!');
          $('#customerForm')[0].reset();
          addCustomerModal.hide();
          loadCustomers();
          // Auto-select the newly added customer
          setTimeout(() => $('#customerSelect').val(res.id), 100);
        } else {
          alert('Error: ' + (res.msg || 'Unknown error'));
        }
      },
      error: function() {
        alert('Failed to add customer.');
      }
    });
  });

  // Add product row
  $('#addRowBtn').click(function() {
    let row = `
      <tr>
        <td>
          <select class="form-select productSelect" name="product_id[]" required>
            <option value="">Select</option>
            ${products.map(p => `<option value="${p.id}" data-price="${p.price}">${p.name}</option>`).join('')}
          </select>
        </td>
        <td><input type="number" name="quantity[]" class="form-control qtyInput" min="1" value="1" required></td>
        <td><input type="number" name="unit_price[]" class="form-control priceInput" min="0" step="0.01" required></td>
        <td class="subtotal">0.00</td>
        <td><button type="button" class="btn btn-sm btn-danger removeRow">×</button></td>
      </tr>`;
    $('#saleItemsTable tbody').append(row);
  });

  // Auto-fill price when product selected
  $(document).on('change', '.productSelect', function() {
    const price = $(this).find(':selected').data('price') || 0;
    $(this).closest('tr').find('.priceInput').val(price);
    updateTotals();
  });

  // When qty or price changes
  $(document).on('input', '.qtyInput, .priceInput', updateTotals);

  // Remove row
  $(document).on('click', '.removeRow', function() {
    $(this).closest('tr').remove();
    updateTotals();
  });

  function updateTotals() {
    let grandTotal = 0;
    $('#saleItemsTable tbody tr').each(function() {
      const qty = parseFloat($(this).find('.qtyInput').val()) || 0;
      const price = parseFloat($(this).find('.priceInput').val()) || 0;
      const subtotal = qty * price;
      $(this).find('.subtotal').text(subtotal.toFixed(2));
      grandTotal += subtotal;
    });
    $('#grandTotal').text(grandTotal.toFixed(2));
  }

  // Submit sale form via AJAX
  $('#saleForm').submit(function(e) {
    e.preventDefault();
    $.ajax({
      url: 'add_sale.php',
      method: 'POST',
      data: $(this).serialize(),
      success: function(res) {
        if (res.trim() === 'success') {
          alert('Sale added successfully!');
          location.reload();
        } else {
          alert('Error: ' + res);
        }
      },
      error: function() {
        alert('Failed to save sale.');
      }
    });
  });
  </script>

</body>
</html>