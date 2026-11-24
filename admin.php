<?php
session_start();

// Block anyone who is not logged in OR does not have the 'admin' role
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        button { padding: 6px 10px; cursor: pointer; margin-right: 5px; }
        .btn-delete { background-color: #e74c3c; color: white; border: none; }
    </style>
</head>
<body>

    <h1>Admin Panel — User Management</h1>
    <p>Only users with the 'admin' role can see this page. | <a href="logout.php">Logout</a></p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="userTableBody">
            <tr><td colspan="5">Loading users...</td></tr>
        </tbody>
    </table>

    <script>
        const currentAdminId = <?php echo (int)$_SESSION["user_id"]; ?>;

        // Fetch and display all users
        async function loadUsers() {
            const res = await fetch("admin_actions.php");
            const users = await res.json();
            const tbody = document.getElementById("userTableBody");
            tbody.innerHTML = "";

            users.forEach(user => {
                const isSelf = Number(user.id) === currentAdminId;
                const toggleLabel = user.role === "admin" ? "Demote to User" : "Make Admin";

                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${user.id}</td>
                    <td>${escapeHtml(user.name)}</td>
                    <td>${escapeHtml(user.email)}</td>
                    <td><strong>${user.role}</strong></td>
                    <td>
                        ${isSelf ? "<em>(Current You)</em>" : `
                            <button onclick="updateUser('toggle_role', ${user.id}, '${user.role}')">${toggleLabel}</button>
                            <button class="btn-delete" onclick="updateUser('delete', ${user.id})">Delete</button>
                        `}
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Send promote/demote or delete request
        async function updateUser(action, userId, currentRole = "") {
            if (action === "delete" && !confirm("Are you sure you want to delete this user?")) {
                return;
            }

            const res = await fetch("admin_actions.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ action, user_id: userId, current_role: currentRole })
            });

            const data = await res.json();
            if (data.error) {
                alert(data.error);
            } else {
                loadUsers();
            }
        }

        // Prevent XSS when rendering user names/emails
        function escapeHtml(text) {
            const div = document.createElement("div");
            div.textContent = text;
            return div.innerHTML;
        }

        loadUsers();
    </script>

</body>
</html>
