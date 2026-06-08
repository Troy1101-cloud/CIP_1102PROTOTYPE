/**
 * Ralmitrokij Hotel - Plain JavaScript Interactions
 * No frameworks used.
 */

document.addEventListener('DOMContentLoaded', () => {
    // Modal Logic
    const bookingModal = document.getElementById('booking-modal');
    const closeBtn = document.querySelector('.close-modal');
    
    // Function to open modal and prefill room ID
    window.openBookingModal = (roomId, roomName) => {
        if (bookingModal) {
            const roomIdInput = bookingModal.querySelector('input[name="room_id"]');
            const modalTitle = bookingModal.querySelector('.modal-title');
            
            if (roomIdInput) roomIdInput.value = roomId;
            if (modalTitle) modalTitle.innerText = `Reserve: ${roomName}`;
            
            bookingModal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scroll
        }
    };

    if (closeBtn) {
        closeBtn.onclick = () => {
            bookingModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        };
    }

    // Close modal when clicking outside
    window.onclick = (event) => {
        if (event.target == bookingModal) {
            bookingModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    };

    // Client-side Validation Simulation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            let isValid = true;
            const requiredInputs = form.querySelectorAll('[required]');
            
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    
                    // Add error message if not exists
                    let error = input.nextElementSibling;
                    if (!error || !error.classList.contains('error-message')) {
                        error = document.createElement('div');
                        error.classList.add('error-message');
                        error.innerText = 'This field is required.';
                        input.parentNode.insertBefore(error, input.nextSibling);
                    }
                } else {
                    input.classList.remove('is-invalid');
                    const error = input.nextElementSibling;
                    if (error && error.classList.contains('error-message')) {
                        error.remove();
                    }
                }
            });

            if (!isValid) {
                e.preventDefault();
            } else {
                // Show loading state on button
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerText = 'PROCESSING...';
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.7';
                }
            }
        });
    });

    // Sticky Header Scroll Effect
    const header = document.querySelector('.main-header');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
});
