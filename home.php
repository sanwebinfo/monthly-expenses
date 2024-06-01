<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);

header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Strict-Transport-Security: max-age=63072000');
header('X-Robots-Tag: noindex, nofollow', true);

if (!isset($_SESSION['username'])) {
    header('Location: /');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="HandheldFriendly" content="True" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#c7ecee">
<link rel="shortcut icon" href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAA7EAAAOxAGVKw4bAAABqklEQVQ4jZ2Tv0scURDHP7P7SGWh14mkuXJZEH8cgqUWcklAsLBbCEEJSprkD7hD/4BUISHEkMBBiivs5LhCwRQBuWgQji2vT7NeYeF7GxwLd7nl4knMwMDMfL8z876P94TMLt+8D0U0EggQSsAjwMvga8ChJAqxqjTG3m53AQTg4tXHDRH9ABj+zf6oytbEu5d78nvzcyiivx7QXBwy46XOi5z1jbM+Be+nqVfP8yzuD3FM6rzIs9YE1hqGvDf15cVunmdx7w5eYJw1pcGptC9CD4gBUuef5Ujq/BhAlTLIeFYuyfmTZgeYv+2nPt1a371P+Hm1WUPYydKf0lnePwVmh3hnlcO1uc7yvgJUDtdG8oy98kduK2KjeHI0fzCQINSXOk/vlXBUOaihAwnGWd8V5r1uhe1VIK52V6JW2D4FqHZX5lphuwEE7ooyaN7gjLMmKSwYL+pMnV+MA/6+g8RYa2Lg2RBQbj4+rll7uymLy3coiuXb5PdQVf7rKYvojAB8Lf3YUJUHfSYR3XqeLO5JXvk0dhKqSqQQoCO+s5AIxCLa2Lxc6ALcAPwS26XFskWbAAAAAElFTkSuQmCC" />
<?php $current_page = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; echo '<link rel="canonical" href="'.$current_page.'" />'; ?>


    <title>Expense Manager</title>
    <meta name="description" content="A Simple website to Manage your Monthly Expenses and Track your Due date and paid status."/>

    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.3/css/bulma.min.css"
        integrity="sha512-IgmDkwzs96t4SrChW29No3NXBIBv8baW490zk5aXvhCD8vuZM3yUSkbyTBcXohkySecyzIrUwiF/qV0cuPcL3Q=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap"
        rel="stylesheet">

    <style>
        html,
        body {
            min-height: 100vh;
            font-family: "Roboto Mono", monospace;
            background-color: #FDA7DF;
            padding-bottom: 20px;
            font-weight: 600;
            line-height: 1.6;
            word-wrap: break-word;
            -moz-osx-font-smoothing: grayscale;
            -webkit-font-smoothing: antialiased !important;
            -moz-font-smoothing: antialiased !important;
            text-rendering: optimizeLegibility !important;
        }

        input,
        select,
        button {
            font-family: "Roboto Mono", monospace;
        }

        .notification.is-hidden {
            display: none;
        }

        #quote-container {
            margin: 10px auto;
            border-radius: 10px;
            padding: 20px;
            background-color: #fff;
            font-family: "Roboto Mono", monospace;
        }

        #quote {
            font-family: "Roboto Mono", monospace;
            font-size: 20px;
            margin-bottom: 20px;
            color: #333;
        }

        #quote-card {
            margin: 10px auto;
            font-family: "Roboto Mono", monospace;
        }

        .table-container {
            font-family: "Roboto Mono", monospace;
            overflow-x: auto;
        }
        .user-button {
            font-family: "Roboto Mono", monospace;
            display: flex;
            flex-grow: 0.3;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            border-radius: 32px;
            padding: 12px;
           -moz-osx-font-smoothing: grayscale;
           -webkit-font-smoothing: antialiased !important;
           -moz-font-smoothing: antialiased !important;
           text-rendering: optimizeLegibility !important;
        }
    </style>

</head>
<body>

    <section class="section">
        <div class="container">
            <div id="quote-card" class="card is-rounded">
                <div class="card-content">
                    <div id="quote-container">
                        <h1 class="title">Hi, <?php echo $_SESSION['username']; ?></h1>
                        <div class="notification is-hidden" id="notification"></div>
                        <h2 class="subtitle">Add Expense</h2>
                        <form id="expense-form">
                            <div class="field is-horizontal">
                                <div class="field-label is-normal">
                                    <label class="label">Description</label>
                                </div>
                                <div class="field-body">
                                    <div class="field">
                                        <div class="control">
                                            <input class="input is-rounded" type="text" id="description" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="field is-horizontal">
                                <div class="field-label is-normal">
                                    <label class="label">Amount</label>
                                </div>
                                <div class="field-body">
                                    <div class="field">
                                        <div class="control">
                                            <input class="input is-rounded" type="number" min="0" step="0.01" id="amount" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="field is-horizontal">
                                <div class="field-label is-normal">
                                    <label class="label">Status</label>
                                </div>
                                <div class="field-body">
                                    <div class="field">
                                        <div class="control">
                                            <div class="select is-rounded">
                                                <select id="status" required>
                                                    <option value="paid">Paid</option>
                                                    <option value="unpaid">Unpaid</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="field is-horizontal">
                                <div class="field-label is-normal">
                                    <label class="label">Due Date</label>
                                </div>
                                <div class="field-body">
                                    <div class="field">
                                        <div class="control">
                                            <input class="input is-rounded" type="date" id="due-date" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="field is-horizontal">
                                <div class="field-label"></div>
                                <div class="field-body">
                                    <div class="field">
                                        <div class="control">
                                            <button type="submit" class="button is-primary is-rounded user-button">Add Expense</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <hr>

                        <h2 class="subtitle">Expenses</h2>
                        <div class="table-container">
                            <table class="table is-fullwidth is-striped is-hoverable is-bordered">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                        <th>Updated at</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="expense-list"></tbody>
                            </table>
                        </div>
                        <nav class="pagination is-centered" role="navigation" aria-label="pagination">
                        <div class="field is-grouped">
                            <button class="pagination-previous  button is-link is-rounded" id="prev-page">Previous</button>
                            <button class="pagination-next button is-link is-rounded" id="next-page">Next</button>
                        </div>
                        </nav>
                    </div>
                    <hr>
                    <div class="buttons is-centered">
                    <button id="logoutButton" class="button is-danger is-rounded btn-box">Log out</button>
                    </div>
                    <div id="edit-modal" class="modal">
                        <div class="modal-background"></div>
                        <div class="modal-card">
                            <header class="modal-card-head">
                                <p class="modal-card-title">Edit Expense</p>
                                <button class="delete" aria-label="close" onclick="closeModal('edit-modal')"></button>
                            </header>
                            <section class="modal-card-body">
                                <form id="edit-expense-form">
                                    <input type="hidden" id="edit-id">
                                    <div class="field">
                                        <label class="label">Description</label>
                                        <div class="control">
                                            <input class="input is-rounded" type="text" id="edit-description" required>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Amount</label>
                                        <div class="control">
                                            <input class="input is-rounded" type="number" id="edit-amount" min="0" step="0.01" required>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Status</label>
                                        <div class="control">
                                            <div class="select is-rounded">
                                                <select id="edit-status" required>
                                                    <option value="paid">Paid</option>
                                                    <option value="unpaid">Unpaid</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Due Date</label>
                                        <div class="control">
                                            <input class="input is-rounded" type="date" id="edit-due-date" required>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="field is-grouped">
                                        <div class="control">
                                            <button class="button is-link is-rounded user-button" type="submit">Save changes</button>
                                        </div>
                                        <div class="control">
                                            <button class="button is-light is-rounded user-button" type="button" onclick="closeModal('edit-modal')">Cancel</button>
                                        </div>
                                    </div>
                                </form>
                            </section>
                        </div>
                    </div>

                    <div id="delete-modal" class="modal">
                        <div class="modal-background"></div>
                        <div class="modal-card">
                            <header class="modal-card-head">
                                <p class="modal-card-title">Delete Expense</p>
                                <button class="delete" aria-label="close" onclick="closeModal('delete-modal')"></button>
                            </header>
                            <section class="modal-card-body">
                                <p>Are you sure you want to delete this expense?</p>
                                <br>
                                <div class="field is-grouped">
                                    <div class="control">
                                        <button class="button is-danger is-rounded user-button" id="confirm-delete">Delete</button>
                                    </div>
                                    <div class="control">
                                        <button class="button is-light is-rounded user-button" type="button" onclick="closeModal('delete-modal')">Cancel</button>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<script>

const apiUrl = '/api/api.php';
const expenseForm = document.getElementById('expense-form');
const expenseList = document.getElementById('expense-list');
const notification = document.getElementById('notification');
const prevPageBtn = document.getElementById('prev-page');
const nextPageBtn = document.getElementById('next-page');
const editExpenseForm = document.getElementById('edit-expense-form');
const deleteModal = document.getElementById('delete-modal');
const editModal = document.getElementById('edit-modal');
let deleteExpenseId = null;
let totalExpenses = 0;
let currentPage = 0;
const itemsPerPage = 3;

document.addEventListener('DOMContentLoaded', () => {
    loadExpenses();
});

expenseForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const description = sanitizeInput(document.getElementById('description').value.trim());
    const amount = parseFloat(document.getElementById('amount').value.trim());
    const status = sanitizeInput(document.getElementById('status').value.trim());
    const dueDate = sanitizeInput(document.getElementById('due-date').value.trim());
    const username = '<?php echo $_SESSION['username']; ?>';

    if (!description || isNaN(amount) || amount <= 0 || !status || !dueDate) { 
        showNotification('Please fill out all fields correctly with a positive amount', 'is-danger');
        return;
    }

    const expense = { description, amount, status, due_date: dueDate, username: username }; 

    try {
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(expense)
        });

        if (response.ok) {
            showNotification('Expense added successfully', 'is-success');
            expenseForm.reset();
            loadExpenses();
        } else {
            const error = await response.json();
            showNotification(error.message, 'is-danger');
        }
    } catch (error) {
        showNotification('Failed to add expense', 'is-danger');
    }
});

editExpenseForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('edit-id').value;
    const description = sanitizeInput(document.getElementById('edit-description').value.trim());
    const amount = parseFloat(document.getElementById('edit-amount').value.trim());
    const status = sanitizeInput(document.getElementById('edit-status').value.trim());
    const dueDate = sanitizeInput(document.getElementById('edit-due-date').value.trim()); 

    if (!id || !description || isNaN(amount) || amount <= 0 || !status || !dueDate) { 
        showNotification('Please fill out all fields correctly with a positive amount', 'is-danger');
        return;
    }

    const expense = { id, description, amount, status, due_date: dueDate }; 

    try {
        const response = await fetch(apiUrl, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(expense)
        });

        if (response.ok) {
            showNotification('Expense updated successfully', 'is-success');
            closeModal('edit-modal');
            loadExpenses();
        } else {
            const error = await response.json();
            showNotification(error.message, 'is-danger');
        }
    } catch (error) {
        showNotification('Failed to update expense', 'is-danger');
    }
});

async function loadExpenses() {
    try {
        const response = await fetch(`${apiUrl}?limit=${itemsPerPage}&offset=${currentPage * itemsPerPage}`);
        const { expenses, total } = await response.json();
        totalExpenses = total;
        renderExpenses(expenses);
        updatePaginationButtons();
    } catch (error) {
        showNotification('Failed to load expenses', 'is-danger');
    }
}

function renderExpenses(expenses) {
    expenseList.innerHTML = '';
    if (expenses.length === 0) {
        expenseList.innerHTML = '<tr><td colspan="5" class="has-text-centered">No expenses found</td></tr>';
        return;
    }

    expenses.forEach(expense => {
        const row = document.createElement('tr');
        const time = new Date(expense.updated_at);
        const options = {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric',
            second: 'numeric',
            hour12: true
        };
        const formattedTime = time.toLocaleString(undefined, options);
let due_status;
if (expense.status === 'paid') {
    due_status = `<span class="tag is-success">${expense.status}</span>`;
} else {
    due_status = `<span class="tag is-danger">${expense.status}</span>`;
}
row.innerHTML = `
    <td>${expense.description}</td>
    <td>${expense.amount}</td>
    <td>${due_status}</td>
    <td>${expense.due_date}</td>
    <td>${formattedTime}</td>
    <td>
        <div class="field is-grouped">
        <p class="control">
        <button class="button is-small is-info is-rounded user-button" onclick="showEditModal(${expense.id}, '${expense.description}', ${expense.amount}, '${expense.status}', '${expense.due_date}')">📝</button>
        </p>
        <p class="control"> 
        <button class="button is-small is-warning is-rounded user-button" onclick="showDeleteModal(${expense.id})">🗑️</button>
        </p>
        </div>
    </td>
`;

        expenseList.appendChild(row);
    });
}

function showEditModal(id, description, amount, status, dueDate) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-description').value = description;
    document.getElementById('edit-amount').value = amount;
    document.getElementById('edit-status').value = status;
    document.getElementById('edit-due-date').value = dueDate;
    editModal.classList.add('is-active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('is-active');
}

function showDeleteModal(id) {
    deleteExpenseId = id;
    deleteModal.classList.add('is-active');
}

document.getElementById('confirm-delete').addEventListener('click', async () => {
    if (!deleteExpenseId) return;

    try {
        const response = await fetch(apiUrl, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: deleteExpenseId })
        });

        if (response.ok) {
            showNotification('Expense deleted successfully', 'is-success');
            closeModal('delete-modal');
            loadExpenses();
        } else {
            const error = await response.json();
            showNotification(error.message, 'is-danger');
        }
    } catch (error) {
        showNotification('Failed to delete expense', 'is-danger');
    }
});

prevPageBtn.addEventListener('click', () => {
    if (currentPage > 0) {
        currentPage--;
        loadExpenses();
    }
});

nextPageBtn.addEventListener('click', () => {
    const totalPages = Math.ceil(totalExpenses / itemsPerPage);
    const nextPage = currentPage + 1;
    if (nextPage < totalPages) {
        currentPage++;
        loadExpenses();
    }
});

function updatePaginationButtons() {
    const totalPages = Math.ceil(totalExpenses / itemsPerPage);
    prevPageBtn.disabled = currentPage === 0 || totalPages <= 1;
    nextPageBtn.disabled = (currentPage + 1) * itemsPerPage >= totalExpenses || currentPage + 1 === totalPages || totalPages <= 1;
}

function showNotification(message, type) {
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.classList.remove('is-hidden');
    setTimeout(() => {
        notification.classList.add('is-hidden');
    }, 3000);
}

function sanitizeInput(input) {
    return input.replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

document.addEventListener('DOMContentLoaded', function() {
    const logoutButton = document.getElementById('logoutButton');

    logoutButton.addEventListener('click', function() {
        logout();
    });
});

function logout() {
    fetch('/logout.php', {
        method: 'GET',

    })
    .then(response => {
            return response.json();
    })
    .then(data => {
        if (data.message) {
            window.location.href = '/';
        } else {
            console.log(data.message);
        }
    })
    .catch(error => {
        console.log(error.message);
    });
}

</script>

</body>
</html>