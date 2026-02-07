<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lost_found_db"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error . " <br>Please check your database name in student_ui.php");
}


$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_student_action'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];

    $stmt = $conn->prepare("INSERT INTO students (first_name, last_name, email, dob) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $first_name, $last_name, $email, $dob);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Student added successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
    header("Location: student_ui.php"); 
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_student_action'])) {
    $id = $_POST['student_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];

    $stmt = $conn->prepare("UPDATE students SET first_name=?, last_name=?, email=?, dob=? WHERE id=?");
    $stmt->bind_param("ssssi", $first_name, $last_name, $email, $dob, $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Student updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating: " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
    header("Location: student_ui.php");
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM students WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Student deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting: " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
    header("Location: student_ui.php");
    exit();
}

$students = [];
$sql = "SELECT * FROM students ORDER BY id DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Students - GMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-gradient { background: linear-gradient(180deg, #4f46e5 0%, #3730a3 100%); }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c7c7c7; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

<div class="flex h-screen overflow-hidden">
    
    <aside class="w-64 sidebar-gradient text-white flex flex-col shadow-2xl transition-all duration-300 hidden md:flex">
        <div class="h-20 flex items-center justify-center border-b border-indigo-500/30">
            <div class="flex items-center gap-3">
                <i class="fas fa-graduation-cap text-3xl text-yellow-300"></i>
                <div class="leading-tight">
                    <h1 class="text-lg font-bold tracking-wide">GMS</h1>
                    <span class="text-xs text-indigo-200">Admin Portal</span>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <p class="px-4 text-xs font-semibold text-indigo-300 uppercase tracking-wider mb-2">Main Menu</p>
            <a href="dashboard.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-indigo-100 hover:bg-white/10 hover:text-white transition-all">
                <i class="fas fa-tachometer-alt w-5"></i> Dashboard
            </a>
            <a href="student_ui.php" class="flex items-center gap-4 px-4 py-3 rounded-xl bg-white/10 text-white font-medium shadow-sm transition-all">
                <i class="fas fa-user-graduate w-5"></i> Students
            </a>
            <a href="teacher_ui.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-indigo-100 hover:bg-white/10 hover:text-white transition-all">
                <i class="fas fa-chalkboard-teacher w-5"></i> Teachers
            </a>
        </nav>
        
        <div class="p-4 border-t border-indigo-500/30">
            <a href="login.php" class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-red-100 hover:bg-red-500/20 transition-all">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <header class="bg-white h-16 shadow-sm flex items-center justify-between px-8 z-10 shrink-0">
            <div class="flex items-center gap-4">
                <button class="md:hidden text-gray-600 text-xl"><i class="fas fa-bars"></i></button>
                <h1 class="text-xl font-bold text-gray-700">Student Management</h1>
            </div>
            
            <button onclick="openModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2 transform active:scale-95">
                <i class="fas fa-plus"></i> <span class="hidden sm:inline">Add New Student</span>
            </button>
        </header>

        <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-gray-50">
            
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg flex items-center gap-3 shadow-sm animate-fade-in-down <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'; ?>">
                    <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> text-xl"></i>
                    <div>
                        <p class="font-bold"><?php echo $message_type === 'success' ? 'Success!' : 'Error!'; ?></p>
                        <p class="text-sm"><?php echo htmlspecialchars($message); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Student Info</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Email</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider hidden md:table-cell">DOB</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-user-graduate text-4xl text-gray-300 mb-3"></i>
                                            <p>No students found in the database.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($students as $student): ?>
                                <tr class="hover:bg-indigo-50/30 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold text-sm">
                                                <?php echo strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)); ?>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></div>
                                                <div class="text-xs text-gray-400">ID: #<?php echo $student['id']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">
                                        <?php echo htmlspecialchars($student['email']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                                        <?php echo htmlspecialchars($student['dob'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-3">
                                            <button onclick='openModal(<?php echo json_encode($student); ?>)' class="text-indigo-600 hover:text-indigo-900 p-1 hover:bg-indigo-100 rounded transition-colors" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="student_ui.php?action=delete&id=<?php echo $student['id']; ?>" class="text-red-400 hover:text-red-600 p-1 hover:bg-red-100 rounded transition-colors" onclick="return confirm('Are you sure you want to delete this student permanently?');" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<div id="studentModal" class="fixed inset-0 bg-gray-900/60 hidden items-center justify-center z-50 backdrop-blur-sm transition-opacity opacity-0" style="transition: opacity 0.3s ease;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 transform scale-95 transition-transform duration-300" id="modalContent">
        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-user-circle text-indigo-500"></i>
                <span id="modalTitle">Add New Student</span>
            </h2>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="student_ui.php" method="POST" id="studentForm">
            <input type="hidden" name="student_id" id="student_id">
            <input type="hidden" name="add_student_action" id="add_action" value="1">
            <input type="hidden" name="update_student_action" id="update_action" disabled>

            <div class="space-y-5">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide">First Name</label>
                        <input type="text" name="first_name" id="first_name" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all placeholder-gray-400" placeholder="John">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Last Name</label>
                        <input type="text" name="last_name" id="last_name" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all placeholder-gray-400" placeholder="Doe">
                    </div>
                </div>
                
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Email Address</label>
                    <div class="relative">
                        <span class="absolute left-4 top-2.5 text-gray-400"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" id="email" required class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all placeholder-gray-400" placeholder="john.doe@example.com">
                    </div>
                </div>
                
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Date of Birth</label>
                    <input type="date" name="dob" id="dob" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all text-gray-600">
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button type="button" onclick="closeModal()" class="flex-1 px-4 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-xl hover:bg-gray-50 font-semibold transition-colors shadow-sm">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 font-semibold shadow-md hover:shadow-lg transition-all transform active:scale-95 flex justify-center items-center gap-2" id="modalSubmitBtn">
                    <i class="fas fa-save"></i> <span>Save Student</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('studentModal');
    const modalContent = document.getElementById('modalContent');
    const form = document.getElementById('studentForm');
    const modalTitle = document.getElementById('modalTitle');
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');
    

    const inpId = document.getElementById('student_id');
    const inpFirst = document.getElementById('first_name');
    const inpLast = document.getElementById('last_name');
    const inpEmail = document.getElementById('email');
    const inpDob = document.getElementById('dob');
    
    const addAction = document.getElementById('add_action');
    const updateAction = document.getElementById('update_action');

    function openModal(data = null) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
        }, 10);
        
        modal.classList.add('flex');
        
        if (data) {
            modalTitle.innerText = "Edit Student Details";
            modalSubmitBtn.innerHTML = '<i class="fas fa-check"></i> <span>Update Changes</span>';
            modalSubmitBtn.classList.replace('bg-indigo-600', 'bg-emerald-600');
            modalSubmitBtn.classList.replace('hover:bg-indigo-700', 'hover:bg-emerald-700');
            
            inpId.value = data.id;
            inpFirst.value = data.first_name;
            inpLast.value = data.last_name;
            inpEmail.value = data.email;
            inpDob.value = data.dob || '';
            
            addAction.disabled = true;
            updateAction.disabled = false;
        } else {
            resetForm();
        }
    }

    function closeModal() {
        modal.classList.add('opacity-0');
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            resetForm();
        }, 300);
    }

    function resetForm() {
        form.reset();
        modalTitle.innerText = "Add New Student";
        modalSubmitBtn.innerHTML = '<i class="fas fa-save"></i> <span>Save Student</span>';
        modalSubmitBtn.classList.replace('bg-emerald-600', 'bg-indigo-600');
        modalSubmitBtn.classList.replace('hover:bg-emerald-700', 'hover:bg-indigo-700');
        
        inpId.value = '';
        addAction.disabled = false;
        updateAction.disabled = true;
    }

    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });
</script>

<style>
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-down {
        animation: fadeInDown 0.3s ease-out;
    }
</style>

</body>
</html>