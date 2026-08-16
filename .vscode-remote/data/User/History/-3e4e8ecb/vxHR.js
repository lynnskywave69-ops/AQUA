document.addEventListener('DOMContentLoaded', function () {
    const planSelect = document.getElementById('planSelect');
    const expectedReturn = document.getElementById('expectedReturn');

    // Investment plans with fixed returns
    const investmentPlans = {
        'Plan 1': { min: 300, profit: 20, days: 25 },
        'Plan 2': { min: 700, profit: 40, days: 30 },
        'Plan 3': { min: 1200, profit: 50, days: 60 },
        'Plan 4': { min: 2500, profit: 100, days: 90 },
        'Plan 5': { min: 4800, profit: 200, days: 130 },
        'Plan 6': { min: 9000, profit: 390, days: 180 },
        'Plan 7': { min: 18000, profit: 800, days: 210 }
    };

    function updateExpectedReturn() {
        if (!planSelect || !expectedReturn) return;

        const selectedPlan = planSelect.value;
        const planInfo = investmentPlans[selectedPlan];
        
        if (!planInfo) return;

        const amountInput = document.querySelector('input[name="amount"]');
        const amount = amountInput && amountInput.value ? parseFloat(amountInput.value) : 0;
        
        if (amount >= planInfo.min) {
            expectedReturn.textContent = 'KSh ' + planInfo.profit.toFixed(2);
        } else if (amount > 0) {
            expectedReturn.textContent = 'Minimum KSh ' + planInfo.min.toFixed(0) + ' required';
        } else {
            expectedReturn.textContent = 'KSh 0.00';
        }
    }

    if (planSelect) {
        planSelect.addEventListener('change', updateExpectedReturn);
    }

    const amountInput = document.querySelector('input[name="amount"]');
    if (amountInput) {
        amountInput.addEventListener('input', updateExpectedReturn);
    }

    document.querySelectorAll('.plan-select').forEach(function (button) {
        button.addEventListener('click', function () {
            const plan = this.dataset.plan;
            if (planSelect) {
                planSelect.value = plan;
            }

            const amountInput = document.querySelector('input[name="amount"]');
            if (amountInput) {
                const planInfo = investmentPlans[plan];
                if (planInfo) {
                    amountInput.value = planInfo.min;
                    amountInput.focus();
                }
            }

            updateExpectedReturn();
        });
    });

    updateExpectedReturn();
});
