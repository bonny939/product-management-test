@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-plus-circle"></i> Add New Product
                </h5>
            </div>
            <div class="card-body">
                <form id="productForm" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <label for="name" class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4">
                        <label for="quantity" class="form-label">Quantity in Stock *</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4">
                        <label for="price" class="form-label">Price per Item *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <span class="loading spinner-border spinner-border-sm me-2" role="status"></span>
                            <i class="bi bi-plus-lg"></i> Add Product
                        </button>
                        <button type="button" class="btn btn-secondary" id="cancelEdit" style="display: none;">
                            <i class="bi bi-x-lg"></i> Cancel Edit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul"></i> Products List
                </h5>
                <div class="btn-group">
                    <button class="btn btn-outline-primary" id="showActive">
                        <i class="bi bi-list"></i> Active
                    </button>
                    <button class="btn btn-outline-secondary" id="showTrash">
                        <i class="bi bi-trash"></i> Deleted
                    </button>
                    <div class="btn-group">
                        <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-download"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exportProducts('json')">Export as JSON</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportProducts('xml')">Export as XML</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Bulk Actions -->
                <div id="bulkActions" class="mb-3" style="display: none;">
                    <div class="alert alert-info">
                        <span id="selectedCount">0</span> items selected
                        <div class="btn-group ms-3">
                            <button class="btn btn-sm btn-danger" id="bulkDelete">
                                <i class="bi bi-trash"></i> Delete Selected
                            </button>
                            <button class="btn btn-sm btn-success" id="bulkRestore" style="display: none;">
                                <i class="bi bi-arrow-clockwise"></i> Restore Selected
                            </button>
                        </div>
                        <button class="btn btn-sm btn-secondary ms-2" id="clearSelection">
                            <i class="bi bi-x"></i> Clear Selection
                        </button>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Product Name</th>
                                <th>Quantity in Stock</th>
                                <th>Price per Item</th>
                                <th>Date/Time Submitted</th>
                                <th>Total Value($)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="productsTableBody">
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot id="totalRow" class="total-row">
                            <tr>
                                <td colspan="5" class="text-end"><strong>Total Sum:</strong></td>
                                <td id="totalSum"><strong>$0.00</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil"></i> Edit Product
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    @csrf
                    <input type="hidden" id="editId" name="id">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="editQuantity" class="form-label">Quantity in Stock *</label>
                        <input type="number" class="form-control" id="editQuantity" name="quantity" min="0" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="editPrice" class="form-label">Price per Item *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="editPrice" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEdit">
                    <span class="loading spinner-border spinner-border-sm me-2" role="status"></span>
                    <i class="bi bi-check-lg"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage">Are you sure you want to perform this action?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmAction">
                    <span class="loading spinner-border spinner-border-sm me-2" role="status"></span>
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentView = 'active';
    let editingId = null;
    let selectedIds = [];

    // Initialize CSRF token for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Load products on page load
    loadProducts();

    // Form submission
    $('#productForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        if (editingId) {
            updateProduct(editingId, data);
        } else {
            createProduct(data);
        }
    });

    // Edit form submission
    $('#saveEdit').on('click', function() {
        const formData = new FormData(document.getElementById('editForm'));
        const data = Object.fromEntries(formData);
        updateProduct(data.id, data);
    });

    // View toggle buttons
    $('#showActive').on('click', function() {
        currentView = 'active';
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');
        $('#showTrash').removeClass('btn-secondary').addClass('btn-outline-secondary');
        $('#bulkRestore').hide();
        $('#bulkDelete').show();
        loadProducts();
    });

    $('#showTrash').on('click', function() {
        currentView = 'trash';
        $(this).removeClass('btn-outline-secondary').addClass('btn-secondary');
        $('#showActive').removeClass('btn-primary').addClass('btn-outline-primary');
        $('#bulkRestore').show();
        $('#bulkDelete').hide();
        loadTrash();
    });

    // Cancel edit
    $('#cancelEdit').on('click', function() {
        cancelEdit();
    });

    // Select all checkbox
    $('#selectAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.product-checkbox').prop('checked', isChecked);
        updateSelectedIds();
    });

    // Individual checkbox change
    $(document).on('change', '.product-checkbox', function() {
        updateSelectedIds();
    });

    // Bulk actions
    $('#bulkDelete').on('click', function() {
        if (selectedIds.length === 0) {
            showAlert('Please select products to delete.', 'warning');
            return;
        }
        confirmAction('Are you sure you want to delete the selected products?', 'danger', () => {
            bulkDelete(selectedIds);
        });
    });

    $('#bulkRestore').on('click', function() {
        if (selectedIds.length === 0) {
            showAlert('Please select products to restore.', 'warning');
            return;
        }
        confirmAction('Are you sure you want to restore the selected products?', 'success', () => {
            bulkRestore(selectedIds);
        });
    });

    $('#clearSelection').on('click', function() {
        clearSelection();
    });

    // Functions
    function loadProducts() {
        showLoading('#productsTableBody');
        
        $.get('/api/products')
            .done(function(response) {
                if (response.success) {
                    renderProducts(response.data.data, response.total_sum);
                } else {
                    showAlert('Failed to load products', 'danger');
                }
            })
            .fail(function() {
                showAlert('Failed to load products', 'danger');
            });
    }

    function loadTrash() {
        showLoading('#productsTableBody');
        
        $.get('/api/products/trash')
            .done(function(response) {
                if (response.success) {
                    renderProducts(response.data.data, response.data.meta.total_sum, true);
                } else {
                    showAlert('Failed to load deleted products', 'danger');
                }
            })
            .fail(function() {
                showAlert('Failed to load deleted products', 'danger');
            });
    }

    function createProduct(data) {
        setLoading('#productForm button[type="submit"]', true);
        clearValidationErrors('#productForm');
        
        $.post('/api/products', data)
            .done(function(response) {
                if (response.success) {
                    showAlert('Product created successfully!', 'success');
                    $('#productForm')[0].reset();
                    loadProducts();
                } else {
                    showAlert('Failed to create product', 'danger');
                }
            })
            .fail(function(xhr) {
                handleValidationErrors(xhr, '#productForm');
            })
            .always(function() {
                setLoading('#productForm button[type="submit"]', false);
            });
    }

    function updateProduct(id, data) {
        const isModal = $('#editModal').hasClass('show');
        const form = isModal ? '#editForm' : '#productForm';
        const button = isModal ? '#saveEdit' : '#productForm button[type="submit"]';
        
        setLoading(button, true);
        clearValidationErrors(form);
        
        $.ajax({
            url: `/api/products/${id}`,
            method: 'PUT',
            data: data
        })
        .done(function(response) {
            if (response.success) {
                showAlert('Product updated successfully!', 'success');
                if (isModal) {
                    $('#editModal').modal('hide');
                } else {
                    cancelEdit();
                }
                loadProducts();
            } else {
                showAlert('Failed to update product', 'danger');
            }
        })
        .fail(function(xhr) {
            handleValidationErrors(xhr, form);
        })
        .always(function() {
            setLoading(button, false);
        });
    }

    function deleteProduct(id) {
        confirmAction('Are you sure you want to delete this product?', 'danger', () => {
            $.ajax({
                url: `/api/products/${id}`,
                method: 'DELETE'
            })
            .done(function(response) {
                if (response.success) {
                    showAlert('Product deleted successfully!', 'success');
                    loadProducts();
                } else {
                    showAlert('Failed to delete product', 'danger');
                }
            })
            .fail(function() {
                showAlert('Failed to delete product', 'danger');
            });
        });
    }

    function restoreProduct(id) {
        $.post('/api/products/restore', { id: id })
            .done(function(response) {
                if (response.success) {
                    showAlert('Product restored successfully!', 'success');
                    loadTrash();
                } else {
                    showAlert('Failed to restore product', 'danger');
                }
            })
            .fail(function() {
                showAlert('Failed to restore product', 'danger');
            });
    }

    function bulkDelete(ids) {
        $.ajax({
            url: '/api/products/bulk-delete',
            method: 'DELETE',
            data: { ids: ids }
        })
        .done(function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                clearSelection();
                loadProducts();
            } else {
                showAlert('Failed to delete products', 'danger');
            }
        })
        .fail(function() {
            showAlert('Failed to delete products', 'danger');
        });
    }

    function bulkRestore(ids) {
        $.post('/api/products/bulk-restore', { ids: ids })
            .done(function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    clearSelection();
                    loadTrash();
                } else {
                    showAlert('Failed to restore products', 'danger');
                }
            })
            .fail(function() {
                showAlert('Failed to restore products', 'danger');
            });
    }

    function editProduct(product) {
        $('#editId').val(product.id);
        $('#editName').val(product.name);
        $('#editQuantity').val(product.quantity);
        $('#editPrice').val(product.price);
        $('#editModal').modal('show');
    }

    function renderProducts(products, totalSum, isTrash = false) {
        let html = '';
        
        if (products.length === 0) {
            html = `<tr><td colspan="7" class="text-center text-muted">No products found</td></tr>`;
        } else {
            products.forEach(product => {
                const actions = isTrash ? 
                    `<button class="btn btn-sm btn-success" onclick="restoreProduct(${product.id})" title="Restore">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>` :
                    `<div class="btn-group btn-group-sm">
                        <button class="btn btn-primary" onclick="editProduct(${JSON.stringify(product).replace(/"/g, '&quot;')})" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-danger" onclick="deleteProduct(${product.id})" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>`;
                
                html += `
                    <tr class="fade-in" ${isTrash ? 'style="opacity: 0.7;"' : ''}>
                        <td>
                            <input type="checkbox" class="form-check-input product-checkbox" value="${product.id}">
                        </td>
                        <td>${escapeHtml(product.name)}</td>
                        <td>${product.quantity}</td>
                        <td>${product.price}</td>
                        <td>${formatDateTime(product.created_at)}</td>
                        <td>${product.total_value}</td>
                        <td class="table-actions">${actions}</td>
                    </tr>
                `;
            });
        }
        
        $('#productsTableBody').html(html);
        $('#totalSum').html(`<strong>${totalSum || '0.00'}</strong>`);
        
        // Show/hide total row based on view
        $('#totalRow').toggle(!isTrash && products.length > 0);
        
        clearSelection();
    }

    function updateSelectedIds() {
        selectedIds = $('.product-checkbox:checked').map(function() {
            return parseInt($(this).val());
        }).get();
        
        $('#selectedCount').text(selectedIds.length);
        $('#bulkActions').toggle(selectedIds.length > 0);
        
        // Update select all checkbox
        const totalCheckboxes = $('.product-checkbox').length;
        const checkedCheckboxes = selectedIds.length;
        
        if (checkedCheckboxes === 0) {
            $('#selectAll').prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $('#selectAll').prop('indeterminate', false).prop('checked', true);
        } else {
            $('#selectAll').prop('indeterminate', true);
        }
    }

    function clearSelection() {
        selectedIds = [];
        $('.product-checkbox, #selectAll').prop('checked', false);
        $('#bulkActions').hide();
    }

    function cancelEdit() {
        editingId = null;
        $('#productForm')[0].reset();
        $('#productForm button[type="submit"]').html('<i class="bi bi-plus-lg"></i> Add Product');
        $('#cancelEdit').hide();
        clearValidationErrors('#productForm');
    }

    function confirmAction(message, type, callback) {
        $('#confirmMessage').text(message);
        $('#confirmAction').removeClass().addClass(`btn btn-${type}`);
        $('#confirmModal').modal('show');
        
        $('#confirmAction').off('click').on('click', function() {
            $('#confirmModal').modal('hide');
            callback();
        });
    }

    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Remove existing alerts
        $('.alert').remove();
        
        // Add new alert at the top
        $('main').prepend(alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            $('.alert').alert('close');
        }, 5000);
    }

    function setLoading(selector, loading) {
        const $btn = $(selector);
        const $spinner = $btn.find('.loading');
        
        if (loading) {
            $btn.prop('disabled', true);
            $spinner.show();
        } else {
            $btn.prop('disabled', false);
            $spinner.hide();
        }
    }

    function showLoading(selector) {
        $(selector).html(`
            <tr>
                <td colspan="7" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </td>
            </tr>
        `);
    }

    function handleValidationErrors(xhr, formSelector) {
        if (xhr.status === 422) {
            const errors = xhr.responseJSON.errors;
            
            Object.keys(errors).forEach(field => {
                const $field = $(`${formSelector} [name="${field}"]`);
                const $feedback = $field.siblings('.invalid-feedback');
                
                $field.addClass('is-invalid');
                $feedback.text(errors[field][0]);
            });
        } else {
            showAlert('An error occurred. Please try again.', 'danger');
        }
    }

    function clearValidationErrors(formSelector) {
        $(`${formSelector} .is-invalid`).removeClass('is-invalid');
        $(`${formSelector} .invalid-feedback`).text('');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDateTime(dateTimeString) {
        const date = new Date(dateTimeString);
        return date.toLocaleString();
    }

    // Global functions for onclick handlers
    window.editProduct = editProduct;
    window.deleteProduct = deleteProduct;
    window.restoreProduct = restoreProduct;
    window.exportProducts = function(format) {
        window.open(`/api/products/export?format=${format}`, '_blank');
    };
});
</script>
@endpush