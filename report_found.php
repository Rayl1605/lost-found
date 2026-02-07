<?php
session_start();
require_once 'db_connect.php';

$message = "";
$msg_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sanitize Inputs
    $item_name = $conn->real_escape_string($_POST['item_name']);
    $category = $conn->real_escape_string($_POST['category']);
    $date_found = $_POST['date_found'];
    $location = $conn->real_escape_string($_POST['location']);
    $description = $conn->real_escape_string($_POST['description']);
    
    // Get username and ID from session
    $reporter_name = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; 

    // 2. Handle Image Upload
    $image_path = NULL;
    
    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['item_image']['name'];
        $filetype = $_FILES['item_image']['type'];
        $filesize = $_FILES['item_image']['size'];
        
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), $allowed)) {
            $message = "Error: Please select a valid file format (JPG, PNG, GIF).";
            $msg_type = "error";
        } elseif ($filesize > 5 * 1024 * 1024) { 
            $message = "Error: File size is larger than the allowed limit (5MB).";
            $msg_type = "error";
        } else {
            $new_filename = time() . "_found_" . $filename; 
            $upload_dir = "uploads/";
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if(move_uploaded_file($_FILES['item_image']['tmp_name'], $upload_dir . $new_filename)){
                $image_path = $new_filename;
            } else {
                $message = "Error: Failed to upload your image.";
                $msg_type = "error";
            }
        }
    }

    // 3. Insert into Database
    if ($msg_type != "error") {
        $sql = "INSERT INTO found_items (item_name, category, location, date_found, description, reporter_name, image_path, user_id) 
                VALUES ('$item_name', '$category', '$location', '$date_found', '$description', '$reporter_name', '$image_path', '$user_id')";

        if ($conn->query($sql) === TRUE) {
            $message = "Thank you! The item has been recorded. Owners can now see it.";
            $msg_type = "success";
        } else {
            $message = "Database Error: " . $conn->error;
            $msg_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Found Item</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-10">

    <div class="container mx-auto px-4 max-w-2xl">
        
        <div class="flex items-center gap-4 mb-6">
            <a href="dashboard.php" class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow hover:bg-gray-50 transition text-gray-600">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Report Found Item</h1>
        </div>

        <?php if($message): ?>
            <div class="p-4 rounded-xl mb-6 flex items-center gap-3 font-medium <?php echo $msg_type == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <i class="fas <?php echo $msg_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-teal-600 p-6 text-white flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold">Found Item Details</h2>
                    <p class="text-teal-100 text-sm">Help return this item to its owner.</p>
                </div>
                <i class="fas fa-hand-holding-heart text-3xl opacity-50"></i>
            </div>

            <form action="report_found.php" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Item Name</label>
                        <input type="text" name="item_name" required class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="e.g. Blue Umbrella">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category</label>
                        <select name="category" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="Electronics">Electronics</option>
                            <option value="Documents/ID">Documents/ID</option>
                            <option value="Clothing">Clothing</option>
                            <option value="Accessories">Accessories</option>
                            <option value="Personal">Personal Items</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Date Found</label>
                    <input type="date" name="date_found" required class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Location Found (Exact)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-gray-400"><i class="fas fa-map-pin"></i></span>
                        <input type="text" name="location" required class="w-full bg-gray-50 border border-gray-200 rounded-lg pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="e.g. Near Main Gate Guardhouse">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Description</label>
                    <textarea name="description" rows="4" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Describe the item (color, condition, where you left it)..."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Upload Photo (Optional)</label>
                    <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:bg-gray-50 transition cursor-pointer group">
                        <input type="file" name="item_image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewFile(this)">
                        
                        <div id="upload-placeholder">
                            <i class="fas fa-camera text-2xl text-gray-400 mb-2 group-hover:text-teal-500 transition"></i>
                            <p class="text-sm text-gray-500 font-medium">Click to upload photo of found item</p>
                            <p class="text-xs text-gray-400 mt-1">SVG, PNG, JPG or GIF (MAX. 5MB)</p>
                        </div>
                        
                        <div id="preview-container" class="hidden flex-col items-center">
                            <img id="file-preview" class="h-32 object-contain mb-2 rounded shadow-sm">
                            <span id="file-name" class="text-xs text-gray-600 font-medium bg-gray-200 px-2 py-1 rounded"></span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex gap-4">
                    <button type="reset" class="flex-1 py-3 rounded-xl border border-gray-300 text-gray-600 font-bold hover:bg-gray-50 transition" onclick="resetPreview()">Reset</button>
                    <button type="submit" class="flex-1 py-3 rounded-xl bg-teal-600 text-white font-bold shadow-lg hover:bg-teal-700 hover:shadow-xl transition transform active:scale-95">Submit Found Report</button>
                </div>

            </form>
        </div>
    </div>

    <script>
        function previewFile(input) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('file-preview').src = e.target.result;
                    document.getElementById('file-name').innerText = file.name;
                    
                    document.getElementById('upload-placeholder').classList.add('hidden');
                    document.getElementById('preview-container').classList.remove('hidden');
                    document.getElementById('preview-container').classList.add('flex');
                }
                reader.readAsDataURL(file);
            }
        }

        function resetPreview() {
            document.getElementById('upload-placeholder').classList.remove('hidden');
            document.getElementById('preview-container').classList.add('hidden');
            document.getElementById('preview-container').classList.remove('flex');
        }
    </script>
</body>
</html>