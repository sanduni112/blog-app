// basic form validation

function validateRegister() {
    var username = document.getElementById('username').value.trim();
    var email    = document.getElementById('email').value.trim();
    var password = document.getElementById('password').value;
    var confirm  = document.getElementById('confirm_password').value;

    if (username === '') {
        showErr('Username is required.');
        return false;
    }
    if (username.length < 3) {
        showErr('Username must be at least 3 characters.');
        return false;
    }
    if (email === '') {
        showErr('Email is required.');
        return false;
    }
    if (password.length < 6) {
        showErr('Password must be at least 6 characters.');
        return false;
    }
    if (password !== confirm) {
        showErr('Passwords do not match.');
        return false;
    }
    return true;
}

function validateLogin() {
    var username = document.getElementById('username').value.trim();
    var password = document.getElementById('password').value;

    if (username === '') {
        showErr('Username is required.');
        return false;
    }
    if (password === '') {
        showErr('Password is required.');
        return false;
    }
    return true;
}

function validatePost() {
    var title = document.getElementById('title').value.trim();
    var content = '';

    // check if EasyMDE instance is active
    if (typeof easyMDE !== 'undefined' && easyMDE) {
        content = easyMDE.value().trim();
    } else {
        var contentElem = document.getElementById('content');
        if (contentElem) {
            content = contentElem.value.trim();
        }
    }

    if (title === '') {
        showErr('Title is required.');
        return false;
    }
    if (title.length > 255) {
        showErr('Title is too long (max 255 chars).');
        return false;
    }
    if (content === '') {
        showErr('Content cannot be empty.');
        return false;
    }
    return true;
}

function confirmDelete() {
    return confirm('Delete this post? This cannot be undone.');
}

// show error in existing .msg-err element if present, else fallback to alert
function showErr(msg) {
    var el = document.querySelector('.msg-err');
    if (el) {
        el.textContent = msg;
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        alert(msg);
    }
}
