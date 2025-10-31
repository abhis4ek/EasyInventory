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
  <title>Purchases</title>
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
    .purchase-row {
      cursor: pointer;
      transition: background-color 0.2s;
    }
    .purchase-row:hover {
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
    <h2>Purchases</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPurchaseModal">
      <i class="fas fa-plus"></i> Add Purchase
    </button>
  </div>

  <!-- Purchases table -->
  <div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead class="table-light">
        <tr>
          <th style="width: 50px;"></th>
          <th>ID</th>
          <th>Date</th>
          <th>Supplier</th>
          <th>Total</th>
          <th style="width: 200px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $stmt = $conn->prepare("SELECT p.id, p.purchase_date, p.total_amount, s.name AS supplier_name
                FROM purchases p
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                WHERE p.admin_id = ?
                ORDER BY p.purchase_date DESC, p.id DESC");
        $stmt->bind_param('i', $admin_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if($res->num_rows > 0):
          while($row = $res->fetch_assoc()):
            $purchase_id = $row['id'];
            
            // Fetch items for this purchase
            $items_stmt = $conn->prepare("SELECT pi.*, pr.name as product_name
                    FROM purchase_items pi
                    JOIN products pr ON pi.product_id = pr.id
                    WHERE pi.purchase_id = ?");
            $items_stmt->bind_param('i', $purchase_id);
            $items_stmt->execute();
            $items_res = $items_stmt->get_result();
            $items = [];
            while($item = $items_res->fetch_assoc()) {
              $items[] = $item;
            }
            $items_stmt->close();
        ?>
        <tr class="purchase-row" onclick="toggleDetails(<?= $purchase_id ?>)">
          <td class="text-center">
            <i class="fas fa-chevron-right expand-icon" id="icon-<?= $purchase_id ?>"></i>
          </td>
          <td><strong>#<?= $row['id'] ?></strong></td>
          <td><?= date('M d, Y', strtotime($row['purchase_date'])) ?></td>
          <td><?= htmlspecialchars($row['supplier_name'] ?? 'N/A') ?></td>
          <td><strong>₹<?= number_format($row['total_amount'], 2) ?></strong></td>
          <td>
            <button class="btn btn-sm btn-danger action-btn" onclick="event.stopPropagation(); deletePurchase(<?= $purchase_id ?>)">
              <i class="fas fa-trash"></i> Delete
            </button>
          </td>
        </tr>
        <tr class="details-row" id="details-<?= $purchase_id ?>">
          <td colspan="6">
            <div class="p-3">
              <h6><i class="fas fa-box"></i> Purchased Items:</h6>
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
        <tr><td colspan="6" class="text-center py-4">No purchases found.</td></tr>
        <?php 
        endif;
        $stmt->close();
        ?>
      </tbody>
    </table>
  </div>

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
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="supplierForm" novalidate>
          <div class="modal-header">
            <h5 class="modal-title">Add New Supplier</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-12 mb-3">
                <label for="supplierName" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                <input type="text" id="supplierName" name="name" class="form-control" required>
                <div class="error-text" id="supplier_name_error">Name is required</div>
              </div>
              <div class="col-12 mb-3">
                <label for="supplierStreetAddress" class="form-label">Street Address <span class="text-danger">*</span></label>
                <input type="text" id="supplierStreetAddress" name="street_address" class="form-control" required>
                <div class="error-text" id="supplier_street_error">Street address is required</div>
              </div>
              <div class="col-md-4 mb-3">
                <label for="supplierCity" class="form-label">City <span class="text-danger">*</span></label>
                <input type="text" id="supplierCity" name="city" class="form-control" required>
                <div class="error-text" id="supplier_city_error">City is required</div>
              </div>
              <div class="col-md-4 mb-3">
                <label for="supplierPinCode" class="form-label">Pin Code <span class="text-danger">*</span></label>
                <input type="text" id="supplierPinCode" name="pin_code" class="form-control" maxlength="6" required>
                <small class="text-muted">6 digits</small>
                <div class="error-text" id="supplier_pin_error">Pin code must be exactly 6 digits</div>
              </div>
              <div class="col-md-4 mb-3">
                <label for="supplierState" class="form-label">State <span class="text-danger">*</span></label>
                <input type="text" id="supplierState" name="state" class="form-control" required>
                <div class="error-text" id="supplier_state_error">State is required</div>
              </div>
              <div class="col-md-6 mb-3">
                <label for="supplierPhone" class="form-label">Phone <span class="text-danger">*</span></label>
                <input type="tel" id="supplierPhone" name="phone" class="form-control" maxlength="10" required>
                <small class="text-muted">10-digit number (starts with 6-9)</small>
                <div class="error-text" id="supplier_phone_error">Phone must be a valid 10-digit number</div>
              </div>
              <div class="col-md-6 mb-3">
                <label for="supplierEmail" class="form-label">Email</label>
                <input type="email" id="supplierEmail" name="email" class="form-control">
                <small class="text-muted">Optional</small>
                <div class="error-text" id="supplier_email_error">Invalid email format</div>
              </div>
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

  <!-- Add Product Modal -->
  <div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="productForm" novalidate>
          <div class="modal-header">
            <h5 class="modal-title">Add New Product</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="productName" class="form-label">Product Name <span class="text-danger">*</span></label>
              <input type="text" id="productName" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="productCategory" class="form-label">Category <span class="text-danger">*</span></label>
              <div class="input-group">
                <select id="productCategory" name="category_id" class="form-select" required>
                  <option value="">-- Select Category --</option>
                </select>
                <button type="button" class="btn btn-outline-secondary" id="addCategoryBtn">+ New</button>
              </div>
            </div>
            <div class="mb-3">
              <label for="productPrice" class="form-label">Price <span class="text-danger">*</span></label>
              <input type="number" id="productPrice" name="price" class="form-control" step="0.01" min="0" required>
            </div>
            <div class="mb-3">
              <label for="productStock" class="form-label">Initial Stock</label>
              <input type="number" id="productStock" name="stock" class="form-control" min="0" value="0">
            </div>
            <div class="mb-3">
              <label for="productDescription" class="form-label">Description</label>
              <textarea id="productDescription" name="description" class="form-control" rows="2"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Product</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Category Modal -->
  <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="categoryForm" novalidate>
          <div class="modal-header">
            <h5 class="modal-title">Add New Category</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="categoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
              <input type="text" id="categoryName" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="categoryDescription" class="form-label">Description</label>
              <input type="text" id="categoryDescription" name="description" class="form-control">
            </div>
            <div class="mb-3">
              <label for="categoryGstRate" class="form-label">GST Rate (%) <span class="text-danger">*</span></label>
              <input type="number" id="categoryGstRate" name="gst_rate" class="form-control" step="0.01" min="0" max="100" value="0" required>
              <small class="text-muted">Enter rate between 0-100%</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Category</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  let products = [];
  let addSupplierModal, addPurchaseModal, addProductModal, addCategoryModal;
  let currentProductRow = null;

  // Validation patterns
  const phonePattern = /^[6-9]\d{9}$/;
  const pinPattern = /^\d{6}$/;
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  // Validation function
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

  // Toggle details row
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

  // Delete purchase
  function deletePurchase(id) {
    if (!confirm('Are you sure you want to delete this purchase?\n\nThis will also remove all associated items and reverse stock changes.')) {
      return;
    }
    
    $.ajax({
      url: 'delete_purchase.php',
      method: 'POST',
      data: { id: id },
      success: function(res) {
        if (res.trim() === 'success') {
          alert('Purchase deleted successfully!');
          try {
            window.parent.postMessage('refreshDashboard', '*');
          } catch(e) {}
          location.reload();
        } else {
          alert('Error: ' + res);
        }
      },
      error: function() {
        alert('Failed to delete purchase.');
      }
    });
  }

  $(document).ready(function() {
    addSupplierModal = new bootstrap.Modal(document.getElementById('addSupplierModal'));
    addPurchaseModal = new bootstrap.Modal(document.getElementById('addPurchaseModal'));
    addProductModal = new bootstrap.Modal(document.getElementById('addProductModal'));
    addCategoryModal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
  });

  $('#addPurchaseModal').on('show.bs.modal', function() {
    loadSuppliers();
    loadProducts();
  });

  function loadSuppliers() {
    $.getJSON('get_suppliers.php', function(data) {
      let html = '<option value="">-- Select Supplier --</option>';
      data.forEach(s => html += `<option value="${s.id}">${s.name}</option>`);
      $('#supplierSelect').html(html);
    });
  }

  function loadProducts() {
    $.getJSON('get_products.php', function(data) {
      products = data;
    });
  }

  function loadCategories() {
    $.getJSON('get_categories.php', function(data) {
      let html = '<option value="">-- Select Category --</option>';
      data.forEach(c => html += `<option value="${c.id}">${c.name}</option>`);
      $('#productCategory').html(html);
    });
  }

  // Open Add Supplier Modal
  $('#addSupplierBtn').click(function() {
    $('#supplierForm')[0].reset();
    $('.error-text').hide();
    $('.is-invalid').removeClass('is-invalid');
    addSupplierModal.show();
  });

  // Real-time validation for supplier phone
  $('#supplierPhone').on('input', function() {
    this.value = this.value.replace(/\D/g, '').substring(0, 10);
  });

  // Real-time validation for supplier pin code
  $('#supplierPinCode').on('input', function() {
    this.value = this.value.replace(/\D/g, '').substring(0, 6);
  });

  // Submit new supplier form with validation
  $('#supplierForm').submit(function(e) {
    e.preventDefault();
    
    let isValid = true;
    
    isValid &= validateField(
      document.getElementById('supplierName'),
      document.getElementById('supplier_name_error'),
      (v) => v.length > 0,
      'Name is required'
    );
    
    isValid &= validateField(
      document.getElementById('supplierStreetAddress'),
      document.getElementById('supplier_street_error'),
      (v) => v.length > 0,
      'Street address is required'
    );
    
    isValid &= validateField(
      document.getElementById('supplierCity'),
      document.getElementById('supplier_city_error'),
      (v) => v.length > 0,
      'City is required'
    );
    
    isValid &= validateField(
      document.getElementById('supplierPinCode'),
      document.getElementById('supplier_pin_error'),
      (v) => pinPattern.test(v),
      'Pin code must be exactly 6 digits'
    );
    
    isValid &= validateField(
      document.getElementById('supplierState'),
      document.getElementById('supplier_state_error'),
      (v) => v.length > 0,
      'State is required'
    );
    
    isValid &= validateField(
      document.getElementById('supplierPhone'),
      document.getElementById('supplier_phone_error'),
      (v) => phonePattern.test(v),
      'Phone must be a valid 10-digit number'
    );
    
    const emailField = document.getElementById('supplierEmail');
    const emailValue = emailField.value.trim();
    if (emailValue.length > 0) {
      isValid &= validateField(
        emailField,
        document.getElementById('supplier_email_error'),
        (v) => emailPattern.test(v),
        'Invalid email format'
      );
    }
    
    if (!isValid) return;
    
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
          <div class="input-group">
            <select class="form-select productSelect" name="product_id[]" required>
              <option value="">Select</option>
              ${products.map(p => `<option value="${p.id}" data-price="${p.price}">${p.name}</option>`).join('')}
            </select>
            <button type="button" class="btn btn-outline-secondary btn-sm addNewProductBtn">+ New</button>
          </div>
        </td>
        <td><input type="number" name="quantity[]" class="form-control qtyInput" min="1" value="1" required></td>
        <td><input type="number" name="unit_price[]" class="form-control priceInput" min="0" step="0.01" required></td>
        <td class="subtotal">0.00</td>
        <td><button type="button" class="btn btn-sm btn-danger removeRow">×</button></td>
      </tr>`;
    $('#purchaseItemsTable tbody').append(row);
  });

  // Open Add Product Modal
  $(document).on('click', '.addNewProductBtn', function() {
    currentProductRow = $(this).closest('tr');
    loadCategories();
    $('#productForm')[0].reset();
    addProductModal.show();
  });

  // Open Add Category Modal
  $('#addCategoryBtn').click(function() {
    $('#categoryForm')[0].reset();
    addCategoryModal.show();
  });

  // Submit new category form
  $('#categoryForm').submit(function(e) {
    e.preventDefault();
    $.ajax({
      url: 'add_category.php',
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.status === 'success') {
          alert('Category added successfully!');
          $('#categoryForm')[0].reset();
          addCategoryModal.hide();
          loadCategories();
          setTimeout(() => $('#productCategory').val(res.id), 100);
        } else {
          alert('Error: ' + (res.msg || 'Unknown error'));
        }
      },
      error: function() {
        alert('Failed to add category.');
      }
    });
  });

  // Submit new product form
  $('#productForm').submit(function(e) {
    e.preventDefault();
    $.ajax({
      url: 'add_product.php',
      method: 'POST',
      data: $(this).serialize(),
      success: function(res) {
        if (res.trim() === 'success') {
          alert('Product added successfully!');
          $('#productForm')[0].reset();
          addProductModal.hide();
          loadProducts();
          setTimeout(() => {
            refreshProductDropdowns();
          }, 200);
        } else {
          alert('Error: ' + res);
        }
      },
      error: function() {
        alert('Failed to add product.');
      }
    });
  });

  function refreshProductDropdowns() {
    $('.productSelect').each(function() {
      const currentVal = $(this).val();
      let html = '<option value="">Select</option>';
      products.forEach(p => {
        html += `<option value="${p.id}" data-price="${p.price}">${p.name}</option>`;
      });
      $(this).html(html);
      
      if (currentVal) {
        $(this).val(currentVal);
      }
      
      if (currentProductRow && $(this).closest('tr').is(currentProductRow)) {
        if (products.length > 0) {
          const lastProduct = products[products.length - 1];
          $(this).val(lastProduct.id);
          $(this).closest('tr').find('.priceInput').val(lastProduct.price);
          updateTotals();
        }
      }
    });
    currentProductRow = null;
  }

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
    $('#purchaseItemsTable tbody tr').each(function() {
      const qty = parseFloat($(this).find('.qtyInput').val()) || 0;
      const price = parseFloat($(this).find('.priceInput').val()) || 0;
      const subtotal = qty * price;
      $(this).find('.subtotal').text(subtotal.toFixed(2));
      grandTotal += subtotal;
    });
    $('#grandTotal').text(grandTotal.toFixed(2));
  }

  $('#purchaseForm').submit(function(e) {
    e.preventDefault();
    $.ajax({
      url: 'add_purchase.php',
      method: 'POST',
      data: $(this).serialize(),
      success: function(res) {
        if (res.trim() === 'success') {
          alert('Purchase added successfully!');
          
          try {
            window.parent.postMessage('refreshDashboard', '*');
          } catch(e) {}
          
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