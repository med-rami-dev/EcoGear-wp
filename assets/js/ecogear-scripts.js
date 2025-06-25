// EcoGear Simple & Reliable JavaScript with QR Code Support

// QR Code generation function
function generateQRCode(text, containerId) {
    // Use a simple QR code API service
    const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(text)}`;

    const container = document.getElementById(containerId);
    if (container) {
        container.innerHTML = `<img src="${qrApiUrl}" alt="QR Code" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">`;
    }
}

// Show QR code modal
function showQRCode(text, title) {
    // Remove existing modal
    const existingModal = document.querySelector('.eco-qr-modal');
    if (existingModal) {
        existingModal.remove();
    }

    // Create modal
    const modal = document.createElement('div');
    modal.className = 'eco-qr-modal';
    modal.innerHTML = `
        <div class="eco-qr-content">
            <h3>📱 ${title}</h3>
            <p style="color: #64748b; margin-bottom: 1rem;">Scan this QR code to copy the value</p>
            <div class="eco-qr-code" id="qr-container"></div>
            <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; margin: 1rem 0; word-break: break-all; font-family: monospace; font-size: 0.85rem; color: #374151;">
                ${text}
            </div>
            <button class="eco-qr-close" onclick="closeQRModal()">Close</button>
        </div>
    `;

    document.body.appendChild(modal);

    // Generate QR code
    generateQRCode(text, 'qr-container');

    // Show modal
    setTimeout(() => {
        modal.classList.add('active');
    }, 10);

    // Close on backdrop click
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeQRModal();
        }
    });
}

// Close QR modal
function closeQRModal() {
    const modal = document.querySelector('.eco-qr-modal');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.remove();
        }, 300);
    }
}

// Get current domain
function getCurrentDomain() {
    return window.location.hostname || 'localhost';
}

// Modern copy to clipboard with visual feedback
async function copyToClipboard(element) {
    try {
        let textToCopy;
        if (typeof element === 'string') {
            textToCopy = element;
        } else {
            textToCopy = element.value || element.textContent;
        }

        // Add visual feedback immediately
        if (element.style) {
            element.style.transform = 'scale(0.98)';
            element.style.backgroundColor = '#dcfce7';
        }

        // Copy to clipboard
        await navigator.clipboard.writeText(textToCopy);

        // Show success notification
        showNotification('Copied to clipboard! ✅', 'success');

        // Add success visual feedback
        if (element.style) {
            element.style.borderColor = '#22c55e';
            element.style.backgroundColor = '#ecfdf5';

            // Select the text for visual confirmation
            if (element.select) {
                element.select();
                element.setSelectionRange(0, 99999);
            }
        }

    } catch (err) {
        // Fallback for older browsers
        if (element.select) {
            element.select();
            element.setSelectionRange(0, 99999);

            try {
                document.execCommand('copy');
                showNotification('Copied to clipboard! ✅', 'success');
            } catch (fallbackErr) {
                showNotification('Copy failed. Please select and copy manually.', 'error');
            }
        } else {
            // For string inputs, create temporary textarea
            const textarea = document.createElement('textarea');
            textarea.value = textToCopy;
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                showNotification('Copied to clipboard! ✅', 'success');
            } catch (fallbackErr) {
                showNotification('Copy failed. Please select and copy manually.', 'error');
            }
            document.body.removeChild(textarea);
        }
    }

    // Reset visual feedback after animation
    if (element.style) {
        setTimeout(() => {
            element.style.transform = '';
            element.style.backgroundColor = '';
            element.style.borderColor = '';
        }, 300);
    }
}

// Modern notification system
function showNotification(message, type = 'success') {
    // Remove existing notifications
    const existing = document.querySelectorAll('.eco-notification');
    existing.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `eco-notification eco-notification-${type}`;
    notification.innerHTML = `
        <div class="eco-notification-content">
            <span class="eco-notification-icon">${type === 'success' ? '✅' : '❌'}</span>
            <span class="eco-notification-text">${message}</span>
        </div>
    `;

    // Apply styles
    const styles = {
        position: 'fixed',
        top: '20px',
        right: '20px',
        background: type === 'success'
            ? 'linear-gradient(135deg, #dcfce7, #bbf7d0)'
            : 'linear-gradient(135deg, #fef2f2, #fee2e2)',
        color: type === 'success' ? '#166534' : '#dc2626',
        padding: '16px 24px',
        borderRadius: '12px',
        fontWeight: '600',
        zIndex: '10000',
        boxShadow: '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
        border: `1px solid ${type === 'success' ? '#22c55e' : '#f87171'}`,
        backdropFilter: 'blur(8px)',
        transform: 'translateX(400px)',
        transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
        maxWidth: '300px'
    };

    Object.assign(notification.style, styles);

    // Add notification content styles
    const contentStyles = {
        display: 'flex',
        alignItems: 'center',
        gap: '12px'
    };

    const content = notification.querySelector('.eco-notification-content');
    Object.assign(content.style, contentStyles);

    // Add to DOM
    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);

    // Animate out and remove
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        notification.style.opacity = '0';

        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    console.log('EcoGear JavaScript initializing...');

    // Add smooth hover effects to cards
    const cards = document.querySelectorAll('.eco-button-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = '';
        });
    });

    // Add focus enhancement to inputs
    const inputs = document.querySelectorAll('input[type="text"], input[readonly]');
    inputs.forEach(input => {
        input.addEventListener('focus', function () {
            this.style.transform = 'scale(1.02)';
        });

        input.addEventListener('blur', function () {
            this.style.transform = '';
        });
    });

    // Add simple click feedback to buttons (no loading state to avoid getting stuck)
    const buttons = document.querySelectorAll('.eco-btn');
    buttons.forEach(button => {
        button.addEventListener('click', function (e) {
            // Simple visual feedback
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });

    // Simple scroll animations
    const animationObserver = new IntersectionObserver(function (entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    // Apply scroll animations
    const animatedElements = document.querySelectorAll('.eco-button-card, .eco-keys, .eco-form');
    animatedElements.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = `all 0.6s cubic-bezier(0.4, 0, 0.2, 1) ${index * 0.1}s`;
        animationObserver.observe(el);
    });

    // Auto-generate QR code if API keys are present
    autoGenerateQRIfKeysPresent();

    console.log('EcoGear JavaScript loaded successfully!');
});

// Add smooth hover effects to cards
const cards = document.querySelectorAll('.eco-button-card');
cards.forEach(card => {
    card.addEventListener('mouseenter', function () {
        this.style.transform = 'translateY(-8px) scale(1.02)';
    });

    card.addEventListener('mouseleave', function () {
        this.style.transform = '';
    });
});

// Add focus enhancement to inputs
const inputs = document.querySelectorAll('input[type="text"], input[readonly]');
inputs.forEach(input => {
    input.addEventListener('focus', function () {
        this.style.transform = 'scale(1.02)';
    });

    input.addEventListener('blur', function () {
        this.style.transform = '';
    });
});

// Add ripple effect to buttons
buttons.forEach(button => {
    button.addEventListener('click', function (e) {
        const ripple = document.createElement('span');
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;

        ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.4);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
            `;

        // Add ripple animation
        if (!document.querySelector('#ripple-style')) {
            const rippleStyle = document.createElement('style');
            rippleStyle.id = 'ripple-style';
            rippleStyle.textContent = `
                    @keyframes ripple {
                        to {
                            transform: scale(4);
                            opacity: 0;
                        }
                    }
                `;
            document.head.appendChild(rippleStyle);
        }

        this.appendChild(ripple);

        setTimeout(() => {
            ripple.remove();
        }, 600);
    });
});
;

// Modern scroll animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function (entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Apply scroll animations when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    const animatedElements = document.querySelectorAll('.eco-button-card, .eco-keys, .eco-form');
    animatedElements.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = `all 0.6s cubic-bezier(0.4, 0, 0.2, 1) ${index * 0.1}s`;
        observer.observe(el);
    });
});

// Generate combined QR code with all API information
function generateCombinedQR() {
    const appId = document.getElementById('app-id').value;
    const appKey = document.getElementById('app-key').value;
    const domain = document.getElementById('domain-name') ? document.getElementById('domain-name').value : getCurrentDomain();
    const fullUrl = document.getElementById('full-url') ? document.getElementById('full-url').value : window.location.href;

    // Create organized data structure
    const combinedData = {
        "EcoGear API Credentials": {
            "Domain": domain,
            "Full URL": fullUrl,
            "APP ID": appId,
            "APP KEY": appKey,
            "Generated": new Date().toISOString()
        }
    };

    // Convert to formatted JSON string
    const dataString = JSON.stringify(combinedData, null, 2);

    // Generate QR code
    const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(dataString)}`; const container = document.getElementById('combined-qr-container');
    if (container) {
        container.innerHTML = `
            <div style="text-align: center; margin: 2rem 1.5rem;">
                <img src="${qrApiUrl}" alt="Combined API QR Code" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            </div>
        `;

        showNotification('QR code automatically generated! 📱✨', 'success');
    }
}

// Auto-generate QR code if API keys are present on page load
function autoGenerateQRIfKeysPresent() {
    // Check if API keys are present in the DOM
    const appIdField = document.getElementById('app-id');
    const appKeyField = document.getElementById('app-key');
    const qrContainer = document.getElementById('combined-qr-container');

    if (appIdField && appKeyField && qrContainer && appIdField.value && appKeyField.value) {
        // Wait a moment for the page to fully render, then generate QR code
        setTimeout(() => {
            console.log('Auto-generating QR code for API keys...');
            generateCombinedQR();
        }, 500);
    } else if (qrContainer && (!appIdField || !appIdField.value || !appKeyField || !appKeyField.value)) {
        // If container exists but no keys, show a fallback message
        setTimeout(() => {
            qrContainer.innerHTML = `
                <div class="eco-qr-fallback" style="text-align: center; padding: 2rem; color: #64748b; border: 2px dashed #d1d5db; border-radius: 8px;">
                    <p>📋 API keys required for QR code generation</p>
                    <p style="font-size: 0.9rem;">Generate your API keys first to see the combined QR code here</p>
                </div>
            `;
        }, 500);
    }
}
