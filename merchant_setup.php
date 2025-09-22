<?php 
session_start();
include 'include/config.php';
$username = $_SESSION['username'];
// Ambil token dari DB
$res = mysqli_query($con, "SELECT merchand, address FROM userx WHERE username='$username'");
$row = mysqli_fetch_assoc($res);

// Cek jika merchant atau address masih kosong/null
if (!empty($row['merchand']) || !isset($_SESSION['username']) ) {
    header("Location: index.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Setup Merchant | Dragon Play - Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
    
    <!-- Cropper.js CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    
    <style>
        .logo-upload-area {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        .logo-upload-area:hover {
            border-color: #007bff;
            background: #e3f2fd;
        }
        .logo-upload-area.dragover {
            border-color: #007bff;
            background: #e3f2fd;
        }
        .logo-preview {
            max-width: 100px;
            max-height: 100px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 5px;
        }
        .loading-spinner {
            display: none;
        }
        
        /* Cropper Modal Styles - Simple */
        .crop-container {
            max-height: 400px;
            overflow: hidden;
        }
        .crop-preview {
            width: 120px;
            height: 120px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #007bff;
            margin: 10px auto;
        }
        .modal-lg {
            max-width: 800px;
        }
        
        /* Cropper customization - Simple */
        .cropper-point {
            width: 8px;
            height: 8px;
            background-color: #007bff;
            border-radius: 50%;
        }
        
        .cropper-line {
            background-color: #007bff;
        }
    </style>
</head>

<body>
    <div class="account-pages my-5 pt-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card overflow-hidden">
                        <div class="bg-primary bg-soft">
                            <div class="row">
                                <div class="col-7">
                                    <div class="text-primary p-4">
                                        <h5 class="text-primary">Setup Your Merchant!</h5>
                                        <p>Dragon Play Billing V2.1</p>
                                    </div>
                                </div>
                                <div class="col-5 align-self-end">
                                    <img src="assets/images/profile-img.png" alt="" class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0"> 
                            <div class="auth-logo">
                                <a href="./" class="auth-logo-light">
                                    <div class="avatar-md profile-user-wid mb-4">
                                        <span class="avatar-title rounded-circle bg-light">
                                            <img src="assets/images/logo-light.svg" alt="" class="rounded-circle" height="34">
                                        </span>
                                    </div>
                                </a>

                                <a href="./" class="auth-logo-dark">
                                    <div class="avatar-md profile-user-wid mb-4">
                                        <span class="avatar-title rounded-circle bg-light">
                                            <img src="assets/images/logo.svg" alt="" class="rounded-circle" height="34">
                                        </span>
                                    </div>
                                </a>
                            </div>
                            
                            <!-- Alert untuk pesan -->
                            <div id="alertMessage" class="alert" style="display: none;"></div>
                            
                            <div class="p-2">
                                <form class="form-horizontal" method="POST" action="controller/savepreps.php" enctype="multipart/form-data" id="merchantForm">
                                   
                                    <!-- Logo Upload Section -->
                                    <div class="mb-3">
                                        <label class="form-label">Merchant Logo</label>
                                        <div class="logo-upload-area" id="logoUploadArea">
                                            <input type="file" class="d-none" id="logoFile" name="logo" accept="image/*">
                                            
                                            <div id="uploadContent">
                                                <i class="bx bx-cloud-upload font-size-36 text-muted"></i>
                                                <p class="text-muted mt-2 mb-1">Click atau drag & drop logo di sini</p>
                                                <p class="text-muted small">Format: JPG, PNG, GIF (Max: 2MB)</p>
                                                <p class="text-primary small"><strong>Logo akan di-crop otomatis menjadi persegi</strong></p>
                                            </div>
                                            
                                            <div id="previewContent" style="display: none;">
                                                <img src="" alt="Logo Preview" class="logo-preview" id="logoPreview">
                                                <p class="text-muted mt-2 mb-0" id="logoFileName"></p>
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" id="editCrop">
                                                        <i class="bx bx-crop"></i> Edit Crop
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" id="removeLogo">
                                                        <i class="bx bx-trash"></i> Hapus
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="error-message" id="logoError"></div>
                                    </div>

                                    <!-- Merchant Name -->
                                    <div class="mb-3">
                                        <label for="merchant" class="form-label">Merchant Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="merchant" name="merchant" placeholder="Enter merchant name" required maxlength="30">
                                        <div class="error-message" id="merchantError"></div>
                                    </div>
            
                                    <!-- Address -->
                                    <div class="mb-3">
                                        <label class="form-label" for="address">Address <span class="text-danger">*</span></label>
                                        <textarea name="address" id="address" class="form-control" placeholder="Enter full address" rows="3" required></textarea>
                                        <div class="error-message" id="addressError"></div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="mt-3 d-grid">
                                        <button class="btn btn-primary waves-effect waves-light" type="submit" id="submitBtn">
                                            <span class="loading-spinner spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            <i class="bx bx-save"></i> Save Merchant Info
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Crop Modal - Simple -->
    <div class="modal fade" id="cropModal" tabindex="-1" aria-labelledby="cropModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cropModalLabel">
                        <i class="bx bx-crop"></i> Crop Logo Menjadi Persegi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="crop-container">
                                <img id="cropImage" style="max-width: 100%;">
                            </div>
                            <div class="mt-2 text-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="resetCenter">
                                    <i class="bx bx-target-lock"></i> Reset Center
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6>Preview:</h6>
                                <div class="crop-preview" id="cropPreview"></div>
                                <small class="text-muted">
                                    Logo akan tampil dalam bentuk persegi<br>
                                    <strong>Drag untuk geser, resize untuk ubah ukuran</strong>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x"></i> Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="cropSave">
                        <i class="bx bx-check"></i> Gunakan Crop Ini
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script src="assets/libs/jquery/jquery.min.js"></script>
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/metismenu/metisMenu.min.js"></script>
    <script src="assets/libs/simplebar/simplebar.min.js"></script>
    <script src="assets/libs/node-waves/waves.min.js"></script>
    <script src="assets/js/app.js"></script>
    
    <!-- Cropper.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <script>
        let cropper = null;
        let currentFile = null;
        let cropModal = null;

        // File validation function
        function validateLogo(file) {
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            const maxSize = 2 * 1024 * 1024; // 2MB
            
            if (!validTypes.includes(file.type)) {
                return 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF.';
            }
            
            if (file.size > maxSize) {
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                return `Ukuran file terlalu besar (${sizeMB}MB). Maksimal 2MB.`;
            }
            
            return null;
        }

        // Show error message
        function showError(elementId, message) {
            const errorEl = document.getElementById(elementId);
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.style.display = 'block';
            }
        }

        // Clear error message
        function clearError(elementId) {
            const errorEl = document.getElementById(elementId);
            if (errorEl) {
                errorEl.textContent = '';
                errorEl.style.display = 'none';
            }
        }

        // Show alert message
        function showAlert(message, type = 'success') {
            const alertEl = document.getElementById('alertMessage');
            alertEl.className = `alert alert-${type}`;
            alertEl.innerHTML = `<i class="bx bx-${type === 'success' ? 'check' : 'error'}-circle"></i> ${message}`;
            alertEl.style.display = 'block';
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                alertEl.style.display = 'none';
            }, 5000);
        }

        // Initialize cropper
        function initCropper(imageSrc) {
            const cropImage = document.getElementById('cropImage');
            cropImage.src = imageSrc;
            
            // Destroy existing cropper
            if (cropper) {
                cropper.destroy();
            }
            
            // Initialize new cropper
            cropper = new Cropper(cropImage, {
                aspectRatio: 1, // Square aspect ratio
                viewMode: 2,
                dragMode: 'move',
                autoCropArea: 0.7,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
                minCropBoxWidth: 100,
                minCropBoxHeight: 100,
                preview: '#cropPreview',
                ready: function() {
                    // Auto center dan optimize crop box saat ready
                    centerCropBox();
                },
                built: function() {
                    // Center ketika cropper selesai dibangun
                    setTimeout(centerCropBox, 100);
                }
            });
        }

        // Function untuk center crop box
        function centerCropBox() {
            if (!cropper) return;
            
            const containerData = cropper.getContainerData();
            const imageData = cropper.getImageData();
            const canvasData = cropper.getCanvasData();
            
            // Hitung ukuran crop box optimal (80% dari ukuran terkecil)
            const minSize = Math.min(canvasData.width, canvasData.height);
            const cropSize = minSize * 0.8;
            
            // Hitung posisi center
            const centerX = canvasData.left + (canvasData.width / 2);
            const centerY = canvasData.top + (canvasData.height / 2);
            
            // Set crop box di tengah
            cropper.setCropBoxData({
                left: centerX - (cropSize / 2),
                top: centerY - (cropSize / 2),
                width: cropSize,
                height: cropSize
            });
        }

        // Show crop modal
        function showCropModal(file) {
            currentFile = file;
            const reader = new FileReader();
            
            reader.onload = function(e) {
                // Show modal first
                cropModal.show();
                
                // Initialize cropper dengan delay untuk ensure modal fully loaded
                setTimeout(() => {
                    initCropper(e.target.result);
                }, 300);
            };
            
            reader.readAsDataURL(file);
        }

        // Convert canvas to blob (improved with better fallback)
        function canvasToBlob(canvas, callback, type = 'image/jpeg', quality = 0.9) {
            if (canvas.toBlob) {
                // Modern browsers
                canvas.toBlob(callback, type, quality);
            } else {
                // Fallback for older browsers
                setTimeout(function() {
                    const dataURL = canvas.toDataURL(type, quality);
                    const binaryString = atob(dataURL.split(',')[1]);
                    const bytes = new Uint8Array(binaryString.length);
                    for (let i = 0; i < binaryString.length; i++) {
                        bytes[i] = binaryString.charCodeAt(i);
                    }
                    const blob = new Blob([bytes], { type: type });
                    callback(blob);
                }, 0);
            }
        }

        // Save cropped image
        function saveCroppedImage() {
            if (!cropper) {
                showAlert('Cropper tidak tersedia!', 'danger');
                return;
            }
            
            console.log('Starting crop save process...');
            
            const canvas = cropper.getCroppedCanvas({
                width: 300,
                height: 300,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });
            
            if (!canvas) {
                showAlert('Gagal membuat canvas crop!', 'danger');
                return;
            }
            
            console.log('Canvas created successfully, size:', canvas.width, 'x', canvas.height);
            
            // Convert canvas to blob
            canvasToBlob(canvas, function(blob) {
                if (!blob) {
                    showAlert('Gagal convert canvas ke blob!', 'danger');
                    return;
                }
                
                console.log('Blob created, size:', blob.size, 'bytes');
                
                // Create object URL for preview
                const croppedImageUrl = URL.createObjectURL(blob);
                
                // Update preview
                document.getElementById('logoPreview').src = croppedImageUrl;
                document.getElementById('logoFileName').textContent = currentFile.name + ' (Cropped)';
                document.getElementById('uploadContent').style.display = 'none';
                document.getElementById('previewContent').style.display = 'block';
                
                // Create new File object from blob untuk mengganti file input
                const croppedFileName = 'cropped_' + currentFile.name.replace(/\.[^/.]+$/, '') + '.jpg';
                const croppedFile = new File([blob], croppedFileName, {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                });
                
                // Replace file input dengan file hasil crop
                const logoFileInput = document.getElementById('logoFile');
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(croppedFile);
                logoFileInput.files = dataTransfer.files;
                
                console.log('File input replaced with cropped file:', croppedFileName);
                console.log('New file size:', croppedFile.size, 'bytes');
                
                // Close modal
                cropModal.hide();
                
                // Clear error
                clearError('logoError');
                
                showAlert('Logo berhasil di-crop dan siap disimpan!', 'success');
                
            }, 'image/jpeg', 0.9);
        }

        // Reset logo upload
        function resetLogoUpload() {
            document.getElementById('logoFile').value = '';
            document.getElementById('uploadContent').style.display = 'block';
            document.getElementById('previewContent').style.display = 'none';
            clearError('logoError');
            currentFile = null;
        }

        // Process selected file
        function processFile(file) {
            const error = validateLogo(file);
            if (error) {
                showError('logoError', error);
                return;
            }
            
            clearError('logoError');
            showCropModal(file);
        }

        // Initialize functionality
        document.addEventListener('DOMContentLoaded', function() {
            const uploadArea = document.getElementById('logoUploadArea');
            const logoFile = document.getElementById('logoFile');
            const removeLogo = document.getElementById('removeLogo');
            const editCrop = document.getElementById('editCrop');
            const cropSave = document.getElementById('cropSave');
            const resetCenter = document.getElementById('resetCenter');
            
            // Initialize modal
            cropModal = new bootstrap.Modal(document.getElementById('cropModal'));

            // Click to upload
            uploadArea.addEventListener('click', function(e) {
                if (!e.target.closest('#removeLogo') && !e.target.closest('#editCrop')) {
                    logoFile.click();
                }
            });

            // File input change
            logoFile.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    processFile(file);
                }
            });

            // Drag and drop
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const file = files[0];
                    
                    // Set file to input
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    logoFile.files = dt.files;
                    
                    processFile(file);
                }
            });

            // Remove logo
            removeLogo.addEventListener('click', function(e) {
                e.stopPropagation();
                resetLogoUpload();
            });

            // Edit crop
            editCrop.addEventListener('click', function(e) {
                e.stopPropagation();
                if (currentFile) {
                    showCropModal(currentFile);
                }
            });

            // Save crop
            cropSave.addEventListener('click', function() {
                saveCroppedImage();
            });

            // Reset center crop
            resetCenter.addEventListener('click', function() {
                centerCropBox();
                showAlert('Crop area di-reset ke center!', 'info');
            });

            // Clean up on modal hide
            document.getElementById('cropModal').addEventListener('hidden.bs.modal', function() {
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
            });

            // Form submission
            document.getElementById('merchantForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Clear previous errors
                clearError('merchantError');
                clearError('addressError');
                clearError('logoError');
                
                const merchant = document.getElementById('merchant').value.trim();
                const address = document.getElementById('address').value.trim();
                const logoFile = document.getElementById('logoFile').files[0];
                
                let hasError = false;
                
                // Validate merchant name
                if (merchant.length < 3) {
                    showError('merchantError', 'Merchant name minimal 3 karakter');
                    hasError = true;
                }
                
                // Validate address
                if (address.length < 10) {
                    showError('addressError', 'Address minimal 10 karakter');
                    hasError = true;
                }
                
                if (hasError) return;
                
                // Debug: Check file info
                if (logoFile) {
                    console.log('Logo file info:');
                    console.log('- Name:', logoFile.name);
                    console.log('- Size:', logoFile.size, 'bytes');
                    console.log('- Type:', logoFile.type);
                    console.log('- Is cropped:', logoFile.name.includes('cropped_'));
                }
                
                // Show loading
                const submitBtn = document.getElementById('submitBtn');
                const spinner = submitBtn.querySelector('.loading-spinner');
                spinner.style.display = 'inline-block';
                submitBtn.disabled = true;
                
                // Submit form
                const formData = new FormData(this);
                
                fetch('controller/savepreps.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Server response:', data);
                    
                    if (data.success) {
                        let message = 'Merchant information saved successfully!';
                        if (logoFile) {
                            message += ' Logo tersimpan.';
                        }
                        showAlert(message, 'success');
                        
                        // Redirect after 2 seconds
                        setTimeout(() => {
                            window.location.href = data.redirect || 'index.php';
                        }, 2000);
                    } else {
                        showAlert(data.message || 'Failed to save merchant information', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while saving', 'danger');
                })
                .finally(() => {
                    spinner.style.display = 'none';
                    submitBtn.disabled = false;
                });
            });
        });
    </script>
</body>
</html>