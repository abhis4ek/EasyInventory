<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';
$admin_id = $_SESSION['admin_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sales</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <style>
    .error-text {
      color: #dc3545;
      font-size: 0.875rem;
      margin-top: 0.25rem;
      display: none;
    }
    .is-invalid {
      border-color: #dc3545;
    }
    .sale-row {
      cursor: pointer;
      transition: background-color 0.2s;
    }
    .sale-row:hover {
      background-color: #f8f9fa;
    }
    .details-row {
      display: none;
      background-color: #f8f9fa;
    }
    .details-table {
      margin: 15px 0;
    }
    .action-btn {
      margin: 0 5px;
    }
    .expand-icon {
      transition: transform 0.3s;
    }
    .expanded .expand-icon {
      transform: rotate(90deg);
    }
  </style>
</head>
<body class="p-3">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Sales</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSaleModal">
      <i class="fas fa-plus"></i> Add Sale
    </button>
  </div>

  <!-- Table of sales -->
  <div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead class="table-light">
        <tr>
          <th style="width: 50px;"></th>
          <th>ID</th>
          <th>Date</th>
          <th>Customer</th>
          <th>Total</th>
          <th style="width: 250px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $stmt = $conn->prepare("SELECT s.id, s.sale_date, s.total_amount, c.name AS customer_name
                FROM sales s
                LEFT JOIN customers c ON s.customer_id = c.id
                WHERE s.admin_id = ?
                ORDER BY s.sale_date DESC, s.id DESC");
        $stmt->bind_param('i', $admin_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if($res->num_rows > 0):
          while($row = $res->fetch_assoc()):
            $sale_id = $row['id'];
            
            // Fetch items for this sale
            $items_stmt = $conn->prepare("SELECT si.*, pr.name as product_name
                    FROM sale_items si
                    JOIN products pr ON si.product_id = pr.id
                    WHERE si.sale_id = ?");
            $items_stmt->bind_param('i', $sale_id);
            $items_stmt->execute();
            $items_res = $items_stmt->get_result();
            $items = [];
            while($item = $items_res->fetch_assoc()) {
              $items[] = $item;
            }
            $items_stmt->close();
        ?>
        <tr class="sale-row" onclick="toggleDetails(<?= $sale_id ?>)">
          <td class="text-center">
            <i class="fas fa-chevron-right expand-icon" id="icon-<?= $sale_id ?>"></i>
          </td>
          <td><strong>#<?= $row['id'] ?></strong></td>
          <td><?= date('M d, Y', strtotime($row['sale_date'])) ?></td>
          <td><?= htmlspecialchars($row['customer_name'] ?? 'Walk-in Customer') ?></td>
          <td><strong>₹<?= number_format($row['total_amount'], 2) ?></strong></td>
          <td>
            <button class="btn btn-sm btn-success action-btn" onclick="event.stopPropagation(); window.open('sale_invoice.php?id=<?= $sale_id ?>', '_blank')">
              <i class="fas fa-file-invoice"></i> Invoice
            </button>
            <button class="btn btn-sm btn-danger action-btn" onclick="event.stopPropagation(); deleteSale(<?= $sale_id ?>)">
              <i class="fas fa-trash"></i> Delete
            </button>
          </td>
        </tr>
        <tr class="details-row" id="details-<?= $sale_id ?>">
          <td colspan="6">
            <div class="p-3">
              <h6><i class="fas fa-shopping-bag"></i> Sold Items:</h6>
              <table class="table table-sm table-striped details-table">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($items as $item): ?>
                  <tr>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>₹<?= number_format($item['unit_price'], 2) ?></td>
                    <td>₹<?= number_format($item['subtotal'], 2) ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </td>
        </tr>
        <?php 
          endwhile;
        else:
        ?>
        <tr><td colspan="6" class="text-center py-4">No sales found.</td></tr>
        <?php 
        endif;
        $stmt->close();
        ?>
      </tbody>
    </table>
  </div>

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
                <select id="customerSelect" name="customer_id" class="form-select"></select>
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
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="customerForm" novalidate>
          <div class="modal-header">
            <h5 class="modal-title">Add New Customer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-12 mb-3">
                <label for="customerName" class="form-label">Customer Name <span class="text-danger">*</span></label>
                <input type="text" id="customerName" name="name" class="form-control" required>
                <div class="error-text" id="customer_name_error">Name is required</div>
              </div>
              <div class="col-12 mb-3">
                <label for="customerStreetAddress" class="form-label">Street Address <span class="text-danger">*</span></label>
                <input type="text" id="customerStreetAddress" name="street_address" class="form-control" required>
                <div class="error-text" id="customer_street_error">Street address is required</div>
              </div>
              <div class="col-md-4 mb-3">
                <label for="customerCity" class="form-label">City <span class="text-danger">*</span></label>
                <input type="text" id="customerCity" name="city" class="form-control" required>
                <div class="error-text" id="customer_city_error">City is required</div>
              </div>
              <div class="col-md-4 mb-3">
                <label for="customerPinCode" class="form-label">Pin Code <span class="text-danger">*</span></label>
                <input type="text" id="customerPinCode" name="pin_code" class="form-control" maxlength="6" required>
                <small class="text-muted">6 digits</small>
                <div class="error-text" id="customer_pin_error">Pin code must be exactly 6 digits</div>
              </div>
              <div class="col-md-4 mb-3">
                <label for="customerState" class="form-label">State <span class="text-danger">*</span></label>
                <input type="text" id="customerState" name="state" class="form-control" required>
                <div class="error-text" id="customer_state_error">State is required</div>
              </div>
              <div class="col-md-6 mb-3">
                <label for="customerPhone" class="form-label">Phone <span class="text-danger">*</span></label>
                <input type="tel" id="customerPhone" name="phone" class="form-control" maxlength="10" required>
                <small class="text-muted">10-digit number (starts with 6-9)</small>
                <div class="error-text" id="customer_phone_error">Phone must be a valid 10-digit number</div>
              </div>
              <div class="col-md-6 mb-3">
                <label for="customerEmail" class="form-label">Email</label>
                <input type="email" id="customerEmail" name="email" class="form-control">
                <small class="text-muted">Optional</small>
                <div class="error-text" id="customer_email_error">Invalid email format</div>
              </div>
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

  const phonePattern = /^[6-9]\d{9}$/;
  const pinPattern = /^\d{6}$/;
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  function validateField(field, errorElement, validationFn, errorMsg) {
    const value = field.value.trim();
    const isValid = validationFn(value);
    
    if (!isValid) {
      field.classList.add('is-invalid');
      if (errorElement) {
        errorElement.style.display = 'block';
        if (errorMsg) errorElement.textContent = errorMsg;
      }
    } else {
      field.classList.remove('is-invalid');
      if (errorElement) errorElement.style.display = 'none';
    }
    
    return isValid;
  }

  function toggleDetails(id) {
    const detailsRow = document.getElementById('details-' + id);
    const icon = document.getElementById('icon-' + id);
    const parentRow = icon.closest('tr');
    
    if (detailsRow.style.display === 'table-row') {
      detailsRow.style.display = 'none';
      parentRow.classList.remove('expanded');
    } else {
      detailsRow.style.display = 'table-row';
      parentRow.classList.add('expanded');
    }
  }

  function deleteSale(id) {
    if (!confirm('Are you sure you want to delete this sale?\n\nThis will also remove all associated items and restore stock.')) {
      return;
    }
    
    $.ajax({
      url: 'delete_sale.php',
      method: 'POST',
      data: { id: id },
      success: function(res) {
        if (res.trim() === 'success') {
          alert('Sale deleted successfully!');
          location.reload();
        } else {
          alert('Error: ' + res);
        }
      },
      error: function() {
        alert('Failed to delete sale.');
      }
    });
  }

  $(document).ready(function() {
    addCustomerModal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
    addSaleModal = new bootstrap.Modal(document.getElementById('addSaleModal'));
  });

  $('#addSaleModal').on('show.bs.modal', function() {
    loadCustomers();

    if (products.length === 0) {
      $.getJSON('get_products.php', function(data) {
        products = data;
      });
    }
  });

  function loadCustomers() {
    $.getJSON('get_customers.php', function(data) {
      let html = '<option value="">-- Walk-in Customer --</option>';
      data.forEach(c => html += `<option value="${c.id}">${c.name}</option>`);
      $('#customerSelect').html(html);
    });
  }

  $('#addCustomerBtn').click(function() {
    $('#customerForm')[0].reset();
    $('.error-text').hide();
    $('.is-invalid').removeClass('is-invalid');
    addCustomerModal.show();
  });

  $('#customerPhone').on('input', function() {
    this.value = this.value.replace(/\D/g, '').substring(0, 10);
  });

  $('#customerPinCode').on('input', function() {
    this.value = this.value.replace(/\D/g, '').substring(0, 6);
  });

  $('#customerForm').submit(function(e) {
    e.preventDefault();
    
    let isValid = true;
    
    isValid &= validateField(
      document.getElementById('customerName'),
      document.getElementById('customer_name_error'),
      (v) => v.length > 0,
      'Name is required'
    );
    
    isValid &= validateField(
      document.getElementById('customerStreetAddress'),
      document.getElementById('customer_street_error'),
      (v) => v.length > 0,
      'Street address is required'
    );
    
    isValid &= validateField(
      document.getElementById('customerCity'),
      document.getElementById('customer_city_error'),
      (v) => v.length > 0,
      'City is required'
    );
    
    isValid &= validateField(
      document.getElementById('customerPinCode'),
      document.getElementById('customer_pin_error'),
      (v) => pinPattern.test(v),
      'Pin code must be exactly 6 digits'
    );
    
    isValid &= validateField(
      document.getElementById('customerState'),
      document.getElementById('customer_state_error'),
      (v) => v.length > 0,
      'State is required'
    );
    
    isValid &= validateField(
      document.getElementById('customerPhone'),
      document.getElementById('customer_phone_error'),
      (v) => phonePattern.test(v),
      'Phone must be a valid 10-digit number'
    );
    
    const emailField = document.getElementById('customerEmail');
    const emailValue = emailField.value.trim();
    if (emailValue.length > 0) {
      isValid &= validateField(
        emailField,
        document.getElementById('customer_email_error'),
        (v) => emailPattern.test(v),
        'Invalid email format'
      );
    }
    
    if (!isValid) return;
    
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

  $('#addRowBtn').click(function() {
    let row = `
      <tr>
        <td>
          <select class="form-select productSelect" name="product_id[]" required>
            <option value="">Select</option>
            ${products.map(p => `<option value="${p.id}" data-price="${p.selling_price}">${p.name}</option>`).join('')}
          </select>
        </td>
        <td><input type="number" name="quantity[]" class="form-control qtyInput" min="1" value="1" required></td>
        <td><input type="number" name="unit_price[]" class="form-control priceInput" min="0" step="0.01" required></td>
        <td class="subtotal">0.00</td>
        <td><button type="button" class="btn btn-sm btn-danger removeRow">×</button></td>
      </tr>`;
    $('#saleItemsTable tbody').append(row);
  });

  $(document).on('change', '.productSelect', function() {
    const price = $(this).find(':selected').data('price') || 0;
    $(this).closest('tr').find('.priceInput').val(price);
    updateTotals();
  });

  $(document).on('input', '.qtyInput, .priceInput', updateTotals);

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