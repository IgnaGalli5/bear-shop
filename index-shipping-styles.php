<style>
/* Estilos para el formulario de envío */
.shipping-form {
    background-color: #f8f8f8;
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
    margin-bottom: 20px;
    border: 1px solid #eee;
}

.shipping-form h4 {
    margin-top: 0;
    margin-bottom: 15px;
    color: var(--color-dark);
    font-size: 1.1rem;
    border-bottom: 1px solid #ddd;
    padding-bottom: 10px;
}

.shipping-origin {
    background-color: #f0f8ff;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 15px;
    font-size: 0.9rem;
    border-left: 3px solid #3498db;
}

.free-shipping-message {
    background-color: #f0fff0;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 15px;
    font-size: 0.9rem;
    border-left: 3px solid #2ecc71;
}

.free-shipping-message p {
    margin: 5px 0;
}

.shipping-form .form-group {
    margin-bottom: 15px;
}

.shipping-form .form-row {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}

.shipping-form .form-row .form-group {
    flex: 1;
    margin-bottom: 0;
}

.shipping-form label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    font-size: 0.9rem;
}

.shipping-form input,
.shipping-form select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: inherit;
    font-size: 0.95rem;
}

.shipping-form input:focus,
.shipping-form select:focus {
    border-color: var(--color-dark);
    outline: none;
    box-shadow: 0 0 0 2px rgba(148, 90, 66, 0.2);
}

.input-with-link {
    position: relative;
}

.zipcode-link {
    display: block;
    font-size: 0.8rem;
    margin-top: 5px;
    color: #3498db;
    text-decoration: none;
}

.zipcode-link:hover {
    text-decoration: underline;
}

.shipping-form button {
    background-color: var(--color-dark);
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s ease;
}

.shipping-form button:hover {
    background-color: #7a4a35;
}

.shipping-result {
    margin-top: 15px;
}

.shipping-result .success-message {
    color: #2ecc71;
    background-color: #f0fff0;
    padding: 10px;
    border-radius: 4px;
    border-left: 3px solid #2ecc71;
}

.shipping-result .error-message {
    color: #e74c3c;
    background-color: #fff0f0;
    padding: 10px;
    border-radius: 4px;
    border-left: 3px solid #e74c3c;
}

.shipping-result .loading-message {
    color: #3498db;
    background-color: #f0f8ff;
    padding: 10px;
    border-radius: 4px;
    border-left: 3px solid #3498db;
}

.shipping-details {
    background-color: #f9f9f9;
    padding: 8px 10px;
    border-radius: 4px;
    margin-top: 8px;
    font-size: 0.9rem;
}

/* Estilos para el resumen del carrito con envío */
.summary-item .free-shipping {
    color: #2ecc71;
    font-weight: bold;
}

#checkout-btn:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

#checkout-btn:disabled:hover {
    background-color: #ccc;
    transform: none;
    box-shadow: none;
}

@media (max-width: 576px) {
    .shipping-form .form-row {
        flex-direction: column;
        gap: 10px;
    }
    
    .shipping-form .form-row .form-group {
        margin-bottom: 10px;
    }
}
</style>

<script src="script-shipping.js"></script>
