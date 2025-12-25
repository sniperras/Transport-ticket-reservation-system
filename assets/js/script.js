// Form validation
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Auto-dismiss alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if (alert.classList.contains('auto-dismiss')) {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        }
    });

    // Date picker restrictions
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        if (!input.min) {
            input.min = new Date().toISOString().split('T')[0];
        }
    });

    // Password strength checker
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            const feedback = this.nextElementSibling;
            if (feedback && feedback.classList.contains('password-feedback')) {
                feedback.textContent = strength.message;
                feedback.className = 'password-feedback ' + strength.class;
            }
        });
    });

    // Seat selection
    const seatElements = document.querySelectorAll('.seat');
    seatElements.forEach(seat => {
        seat.addEventListener('click', function() {
            if (this.classList.contains('available')) {
                this.classList.toggle('selected');
                updateSelectedSeats();
            }
        });
    });

    // Search autocomplete
    const searchInputs = document.querySelectorAll('.search-autocomplete');
    searchInputs.forEach(input => {
        input.addEventListener('input', debounce(function() {
            fetchSuggestions(this.value, this.dataset.type).then(suggestions => {
                showSuggestions(this, suggestions);
            });
        }, 300));
    });
});

// Utility functions
function checkPasswordStrength(password) {
    const strongRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})/;
    const mediumRegex = /^(((?=.*[a-z])(?=.*[A-Z]))|((?=.*[a-z])(?=.*[0-9]))|((?=.*[A-Z])(?=.*[0-9])))(?=.{6,})/;
    
    if (strongRegex.test(password)) {
        return { message: 'Strong password', class: 'text-success' };
    } else if (mediumRegex.test(password)) {
        return { message: 'Medium password', class: 'text-warning' };
    } else {
        return { message: 'Weak password', class: 'text-danger' };
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function updateSelectedSeats() {
    const selectedSeats = document.querySelectorAll('.seat.selected');
    const seatInput = document.querySelector('#selectedSeats');
    if (seatInput) {
        const seatNumbers = Array.from(selectedSeats).map(seat => seat.dataset.seat);
        seatInput.value = seatNumbers.join(',');
    }
}

// AJAX functions
async function fetchSuggestions(query, type) {
    try {
        const response = await fetch(`api/suggest.php?q=${encodeURIComponent(query)}&type=${type}`);
        return await response.json();
    } catch (error) {
        console.error('Error fetching suggestions:', error);
        return [];
    }
}

function showSuggestions(input, suggestions) {
    // Implementation for showing suggestions dropdown
    // This would create a dropdown below the input field
}

// Print ticket
function printTicket(ticketId) {
    const printWindow = window.open(`print_ticket.php?id=${ticketId}`, '_blank');
    printWindow.onload = function() {
        printWindow.print();
    };
}

// Countdown timer for booking
function startBookingTimer(minutes) {
    let time = minutes * 60;
    const timerElement = document.getElementById('bookingTimer');
    
    if (timerElement) {
        const timer = setInterval(() => {
            const minutes = Math.floor(time / 60);
            const seconds = time % 60;
            
            timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (time <= 0) {
                clearInterval(timer);
                alert('Booking session expired. Please start over.');
                window.location.reload();
            }
            
            time--;
        }, 1000);
    }
}