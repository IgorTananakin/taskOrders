// рассчёт суммы заказа
document.addEventListener('DOMContentLoaded', function() {
    const calculateTotal = function(input) {
        const row = input.closest('.row');
        const quantityInput = row.querySelector('input[name*="[quantity]"]');
        const priceInput = row.querySelector('input[name*="[price]"]');
        const totalInput = row.querySelector('input[readonly]');
        
        if (quantityInput && priceInput && totalInput) {
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const total = quantity * price;
            totalInput.value = total.toFixed(2);
        }
    };

    //для изменения количества и цены
    document.querySelectorAll('input[name*="[quantity]"], input[name*="[price]"]').forEach(input => {
        input.addEventListener('input', function() {
            calculateTotal(this);
        });
        
        calculateTotal(input);
    });
});