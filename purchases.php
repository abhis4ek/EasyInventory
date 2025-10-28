<?php
require 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Purchases</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="p-3">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Purchases</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPurchaseModal">
      + Add Purchase
    </button>
  </div>

  <!-- Purchases table -->
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>ID</th>
        <th>Date</th>
        <th>Supplier</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $sql = "SELECT p.id, p.purchase_date, p.total_amount, s.name AS supplier_name
              FROM purchases p
              LEFT JOIN suppliers s ON p.supplier_id = s.id
              ORDER BY p.purchase_date DESC";
      $res = $conn->query($sql);
      while($row = $res->fetch_assoc()):
      ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['purchase_date'] ?></td>
        <td><?= htmlspecialchars($row['supplier_name']) ?></td>
        <td><?= $row['total_amount'] ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <!-- Add Purchase Modal -->
  <div class="modal fade" id="addPurchaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="purchaseForm">
          <div class="modal-header">
            <h5 class="modal-title">Add Purchase</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <div class="mb-3">
              <label for="supplierSelect" class="form-label">Select Supplier</label>
              <div class="input-group">
                <select id="supplierSelect" name="supplier_id" class="form-select" required></select>
                <button type="button" class="btn btn-outline-secondary" id="addSupplierBtn">+ New</button>
              </div>
            </div>

            <table class="table" id="purchaseItemsTable">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Qty</th>
                  <th>Unit Price</th>
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
            <button type="submit" class="btn btn-success">Save Purchase</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Supplier Modal -->
  <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="supplierForm">
          <div class="modal-header">
            <h5 class="modal-title">Add New Supplier</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="supplierName" class="form-label">Name *</label>
              <input type="text" id="supplierName" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="supplierEmail" class="form-label">Email</label>
              <input type="email" id="supplierEmail" name="email" class="form-control">
            </div>
            <div class="mb-3">
              <label for="supplierPhone" class="form-label">Phone</label>
              <input type="text" id="supplierPhone" name="phone" class="form-control">
            </div>
            <div class="mb-3">
              <label for="supplierAddress" class="form-label">Address</label>
              <textarea id="supplierAddress" name="address" class="form-control" rows="2"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Supplier</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  let products = [];
  let addSupplierModal, addPurchaseModal;

  $(document).ready(function() {
    addSupplierModal = new bootstrap.Modal(document.getElementById('addSupplierModal'));
    addPurchaseModal = new bootstrap.Modal(document.getElementById('addPurchaseModal'));
  });

  // Load suppliers & products when modal opens
  $('#addPurchaseModal').on('show.bs.modal', function() {
    loadSuppliers();

    // Load products only once
    if (products.length === 0) {
      $.getJSON('get_products.php', function(data) {
        products = data;
      });
    }
  });

  function loadSuppliers() {
    $.getJSON('get_suppliers.php', function(data) {
      let html = '<option value="">-- Select Supplier --</option>';
      data.forEach(s => html += `<option value="${s.id}">${s.name}</option>`);
      $('#supplierSelect').html(html);
    });
  }

  // Open Add Supplier Modal
  $('#addSupplierBtn').click(function() {
    addSupplierModal.show();
  });

  // Submit new supplier form
  $('#supplierForm').submit(function(e) {
    e.preventDefault();
    $.ajax({
      url: 'add_supplier.php',
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.status === 'success') {
          alert('Supplier added successfully!');
          $('#supplierForm')[0].reset();
          addSupplierModal.hide();
          loadSuppliers();
          // Auto-select the newly added supplier
          setTimeout(() => $('#supplierSelect').val(res.id), 100);
        } else {
          alert('Error: ' + (res.msg || 'Unknown error'));
        }
      },
      error: function() {
        alert('Failed to add supplier.');
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
    $('#purchaseItemsTable tbody').append(row);
  });

  // When product selected, auto-fill price
  $(document).on('change', '.productSelect', function() {
    const price = $(this).find(':selected').data('price') || 0;
    $(this).closest('tr').find('.priceInput').val(price);
    updateTotals();
  });

  // Update total when qty or price changes
  $(document).on('input', '.qtyInput, .priceInput', updateTotals);

  // Remove row
  $(document).on('click', '.removeRow', function() {
    $(this).closest('tr').remove();
    updateTotals();
  });

  function updateTotals() {
    let grandTotal = 0;
    $('#purchaseItemsTable tbody tr').each(function() {
      const qty = parseFloat($(this).find('.qtyInput').val()) || 0;
      const price = parseFloat($(this).find('.priceInput').val()) || 0;
      const subtotal = qty * price;
      $(this).find('.subtotal').text(subtotal.toFixed(2));
      grandTotal += subtotal;
    });
    $('#grandTotal').text(grandTotal.toFixed(2));
  }

  // Submit purchase form
  $('#purchaseForm').submit(function(e) {
    e.preventDefault();
    $.ajax({
      url: 'add_purchase.php',
      method: 'POST',
      data: $(this).serialize(),
      success: function(res) {
        if (res.trim() === 'success') {
          alert('Purchase added successfully!');
          location.reload();
        } else {
          alert('Error: ' + res);
        }
      },
      error: function() {
        alert('Failed to save purchase.');
      }
    });
  });
  </script>

</body>
</html>