/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — INTERACTIVE FOREX RISK CALCULATOR
 * ============================================================================
 * File: assets/js/risk-calculator.js
 *
 * Beginner-Friendly Explanation (CS Year 2, Semester 1):
 * This module calculates:
 *   1. Monetary Risk = Account Balance * (Risk Percentage / 100)
 *      Example: Balance = 100, Risk = 1%  =>  Monetary Risk = 1.00 (in Account Currency)
 *   2. Stop-Loss Distance = |Entry Price - Stop-Loss Price|
 *      Stop-Loss in Pips  = Stop-Loss Distance / Pip Size
 *   3. Pip Value per 1 Standard Lot (in Quote Currency) = Contract Size * Pip Size
 *   4. Estimated Position Size (Standard Lots) =
 *         Monetary Risk / (Stop-Loss in Pips * Pip Value per Standard Lot in Account Currency)
 *
 * Note: This is an educational & illustrative calculation—not personalized financial advice
 * or a recommended risk percentage.
 * ============================================================================
 */

(function () {
    'use strict';

    const form = document.getElementById('forexRiskCalculatorForm');
    if (!form) {
        return;
    }

    const els = {
        balance: document.getElementById('rcAccountBalance'),
        accountCurrency: document.getElementById('rcAccountCurrency'),
        riskPercent: document.getElementById('rcRiskPercent'),
        pair: document.getElementById('rcCurrencyPair'),
        entryPrice: document.getElementById('rcEntryPrice'),
        stopLossPrice: document.getElementById('rcStopLossPrice'),
        takeProfitPrice: document.getElementById('rcTakeProfitPrice'),
        pipSize: document.getElementById('rcPipSize'),
        contractSize: document.getElementById('rcContractSize'),
        conversionRate: document.getElementById('rcConversionRate'),
        conversionHelp: document.getElementById('rcConversionHelp'),
        resetBtn: document.getElementById('rcResetBtn'),
        errorBox: document.getElementById('rcValidationAlert'),

        // Output Elements
        outMonetaryRisk: document.getElementById('rcOutMonetaryRisk'),
        outStopDistancePips: document.getElementById('rcOutStopDistancePips'),
        outStopDistancePrice: document.getElementById('rcOutStopDistancePrice'),
        outStandardLots: document.getElementById('rcOutStandardLots'),
        outMiniLots: document.getElementById('rcOutMiniLots'),
        outMicroLots: document.getElementById('rcOutMicroLots'),
        outUnits: document.getElementById('rcOutUnits'),
        outPipValue: document.getElementById('rcOutPipValue'),
        outDirectionBadge: document.getElementById('rcOutDirectionBadge'),
        outRiskReward: document.getElementById('rcOutRiskReward'),
        outConversionNote: document.getElementById('rcOutConversionNote')
    };

    const PAIR_PRESETS = {
        'EUR/USD': { base: 'EUR', quote: 'USD', pipSize: 0.0001, sampleEntry: 1.0850, sampleStop: 1.0830, sampleTp: 1.0900 },
        'GBP/USD': { base: 'GBP', quote: 'USD', pipSize: 0.0001, sampleEntry: 1.2740, sampleStop: 1.2715, sampleTp: 1.2800 },
        'USD/JPY': { base: 'USD', quote: 'JPY', pipSize: 0.01,   sampleEntry: 151.50, sampleStop: 151.20, sampleTp: 152.25 },
        'AUD/USD': { base: 'AUD', quote: 'USD', pipSize: 0.0001, sampleEntry: 0.6580, sampleStop: 0.6560, sampleTp: 0.6630 },
        'USD/CHF': { base: 'USD', quote: 'CHF', pipSize: 0.0001, sampleEntry: 0.8840, sampleStop: 0.8820, sampleTp: 0.8890 },
        'USD/CAD': { base: 'USD', quote: 'CAD', pipSize: 0.0001, sampleEntry: 1.3620, sampleStop: 1.3645, sampleTp: 1.3560 },
        'EUR/GBP': { base: 'EUR', quote: 'GBP', pipSize: 0.0001, sampleEntry: 0.8540, sampleStop: 0.8520, sampleTp: 0.8585 },
        'GBP/JPY': { base: 'GBP', quote: 'JPY', pipSize: 0.01,   sampleEntry: 192.80, sampleStop: 192.40, sampleTp: 193.80 }
    };

    /**
     * Format a number with a specific number of decimal places.
     */
    function formatNum(value, decimals) {
        if (!Number.isFinite(value)) {
            return '—';
        }
        return value.toLocaleString('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    /**
     * Parse base and quote currencies from the selected pair string (e.g., "EUR/USD").
     */
    function parsePair(pairStr) {
        const parts = String(pairStr || 'EUR/USD').split('/');
        return {
            base: (parts[0] || 'EUR').trim().toUpperCase(),
            quote: (parts[1] || 'USD').trim().toUpperCase()
        };
    }

    /**
     * Synchronize default pip size and conversion rate hint when pair or account currency changes.
     */
    function syncInstrumentDefaults(updateSamplePrices) {
        const pairKey = els.pair.value;
        const preset = PAIR_PRESETS[pairKey];
        const parsed = parsePair(pairKey);
        const acctCurr = (els.accountCurrency.value || 'USD').toUpperCase();

        if (preset) {
            els.pipSize.value = String(preset.pipSize);
            if (updateSamplePrices) {
                els.entryPrice.value = String(preset.sampleEntry);
                els.stopLossPrice.value = String(preset.sampleStop);
                els.takeProfitPrice.value = String(preset.sampleTp);
            }
        }

        const entryVal = parseFloat(els.entryPrice.value);

        if (acctCurr === parsed.quote) {
            // Quote currency equals account currency => 1 unit of quote currency = 1 unit of account currency
            els.conversionRate.value = '1';
            if (els.conversionHelp) {
                els.conversionHelp.textContent = `Account currency (${acctCurr}) matches the quote currency (${parsed.quote}), so conversion factor is 1.0.`;
            }
        } else if (acctCurr === parsed.base && Number.isFinite(entryVal) && entryVal > 0) {
            // Account currency equals base currency (e.g. USD account trading USD/JPY)
            const derivedRate = (1 / entryVal);
            els.conversionRate.value = derivedRate.toFixed(6);
            if (els.conversionHelp) {
                els.conversionHelp.textContent = `Account currency (${acctCurr}) matches the base currency (${parsed.base}); conversion factor derived from 1 / Entry Price (${derivedRate.toFixed(6)}).`;
            }
        } else {
            if (els.conversionHelp) {
                els.conversionHelp.textContent = `Cross-currency calculation: enter the value of 1 ${parsed.quote} in ${acctCurr} to convert pip value into your account currency.`;
            }
        }
    }

    /**
     * Display or hide validation messages.
     */
    function showError(msg) {
        if (!els.errorBox) {
            return;
        }
        if (!msg) {
            els.errorBox.classList.add('d-none');
            els.errorBox.textContent = '';
        } else {
            els.errorBox.classList.remove('d-none');
            els.errorBox.textContent = msg;
        }
    }

    /**
     * Perform the full risk and position size calculation.
     */
    function calculateRisk() {
        const balance = parseFloat(els.balance.value);
        const riskPct = parseFloat(els.riskPercent.value);
        const acctCurr = (els.accountCurrency.value || 'USD').toUpperCase();
        const pairStr = els.pair.value || 'EUR/USD';
        const parsedPair = parsePair(pairStr);
        const entry = parseFloat(els.entryPrice.value);
        const stopLoss = parseFloat(els.stopLossPrice.value);
        const takeProfit = parseFloat(els.takeProfitPrice.value);
        const pipSize = parseFloat(els.pipSize.value);
        const contractSize = parseFloat(els.contractSize.value);

        // Keep conversion rate synced when Account Currency == Base Currency
        if (acctCurr === parsedPair.quote) {
            els.conversionRate.value = '1';
        } else if (acctCurr === parsedPair.base && Number.isFinite(entry) && entry > 0) {
            els.conversionRate.value = (1 / entry).toFixed(6);
        }

        const conversionRate = parseFloat(els.conversionRate.value);

        // Input validation
        if (!Number.isFinite(balance) || balance <= 0) {
            showError('Please enter an account balance greater than 0.');
            return;
        }
        if (!Number.isFinite(riskPct) || riskPct <= 0 || riskPct > 100) {
            showError('Please enter a risk percentage between 0.01% and 100%.');
            return;
        }

        // 1. Monetary Risk (always calculable from Balance & Risk %)
        const monetaryRisk = balance * (riskPct / 100);
        els.outMonetaryRisk.textContent = `${formatNum(monetaryRisk, 2)} ${acctCurr}`;

        if (!Number.isFinite(entry) || entry <= 0 || !Number.isFinite(stopLoss) || stopLoss <= 0) {
            showError('Please enter positive Entry and Stop-Loss prices to calculate stop distance and position size.');
            return;
        }

        if (Math.abs(entry - stopLoss) < 1e-9) {
            showError('Entry price and Stop-Loss price cannot be identical (stop distance must be greater than zero).');
            return;
        }

        if (!Number.isFinite(pipSize) || pipSize <= 0) {
            showError('Pip size must be a positive number (e.g. 0.0001 or 0.01).');
            return;
        }

        if (!Number.isFinite(contractSize) || contractSize <= 0) {
            showError('Contract size must be a positive number (typically 100,000 units per standard lot).');
            return;
        }

        showError('');

        // 2. Trade Direction & Stop Distance
        const isLong = entry > stopLoss;
        const stopDistancePrice = Math.abs(entry - stopLoss);
        const stopDistancePips = stopDistancePrice / pipSize;
        const priceDecimals = pipSize >= 0.01 ? 3 : 5;

        if (els.outDirectionBadge) {
            els.outDirectionBadge.textContent = isLong ? 'LONG (BUY) SETUP' : 'SHORT (SELL) SETUP';
            els.outDirectionBadge.className = isLong
                ? 'badge pg-badge-emerald font-mono'
                : 'badge pg-badge-amber font-mono';
        }

        els.outStopDistancePips.textContent = `${formatNum(stopDistancePips, 1)} pips`;
        els.outStopDistancePrice.textContent = `${formatNum(stopDistancePrice, priceDecimals)} (${parsedPair.quote})`;

        // 3. Optional Risk-to-Reward Assessment
        if (els.outRiskReward) {
            if (Number.isFinite(takeProfit) && takeProfit > 0) {
                const tpValidDirection = (isLong && takeProfit > entry) || (!isLong && takeProfit < entry);
                if (tpValidDirection) {
                    const rewardDistancePrice = Math.abs(takeProfit - entry);
                    const rr = rewardDistancePrice / stopDistancePrice;
                    const rewardPips = rewardDistancePrice / pipSize;
                    els.outRiskReward.textContent = `1 : ${formatNum(rr, 2)} (${formatNum(rewardPips, 1)} pips target)`;
                } else {
                    els.outRiskReward.textContent = 'Check TP side vs Entry';
                }
            } else {
                els.outRiskReward.textContent = 'Optional (enter TP)';
            }
        }

        // 4. Pip Value & Estimated Position Size
        const pipValueInQuote = contractSize * pipSize;
        if (!Number.isFinite(conversionRate) || conversionRate <= 0) {
            els.outPipValue.textContent = `${formatNum(pipValueInQuote, 2)} ${parsedPair.quote} / lot`;
            els.outStandardLots.textContent = 'Conversion rate required';
            els.outMiniLots.textContent = '—';
            els.outMicroLots.textContent = '—';
            els.outUnits.textContent = '—';
            return;
        }

        const pipValueInAccount = pipValueInQuote * conversionRate;
        const riskPerStandardLot = stopDistancePips * pipValueInAccount;
        const standardLots = riskPerStandardLot > 0 ? (monetaryRisk / riskPerStandardLot) : 0;
        const miniLots = standardLots * 10;
        const microLots = standardLots * 100;
        const units = standardLots * contractSize;

        els.outPipValue.textContent = `${formatNum(pipValueInAccount, 2)} ${acctCurr} / std lot`;
        els.outStandardLots.textContent = `${formatNum(standardLots, 2)} lots`;
        els.outMiniLots.textContent = `${formatNum(miniLots, 2)} mini`;
        els.outMicroLots.textContent = `${formatNum(microLots, 2)} micro`;
        els.outUnits.textContent = `${Math.round(units).toLocaleString('en-US')} ${parsedPair.base}`;

        if (els.outConversionNote) {
            if (acctCurr === parsedPair.quote) {
                els.outConversionNote.textContent = `Direct quote match (${acctCurr}): 1 standard lot = ${formatNum(pipValueInAccount, 2)} ${acctCurr}/pip.`;
            } else {
                els.outConversionNote.textContent = `Converted from ${parsedPair.quote} to ${acctCurr} using factor ${conversionRate} (${formatNum(pipValueInQuote, 2)} ${parsedPair.quote}/pip × ${conversionRate}).`;
            }
        }
    }

    // Event Listeners
    els.pair.addEventListener('change', function () {
        syncInstrumentDefaults(true);
        calculateRisk();
    });

    els.accountCurrency.addEventListener('change', function () {
        syncInstrumentDefaults(false);
        calculateRisk();
    });

    [
        els.balance,
        els.riskPercent,
        els.entryPrice,
        els.stopLossPrice,
        els.takeProfitPrice,
        els.pipSize,
        els.contractSize,
        els.conversionRate
    ].forEach(function (input) {
        if (input) {
            input.addEventListener('input', calculateRisk);
        }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        calculateRisk();
    });

    if (els.resetBtn) {
        els.resetBtn.addEventListener('click', function () {
            els.balance.value = '100';
            els.accountCurrency.value = 'USD';
            els.riskPercent.value = '1';
            els.pair.value = 'EUR/USD';
            els.contractSize.value = '100000';
            syncInstrumentDefaults(true);
            calculateRisk();
        });
    }

    // Run initial calculation on page load
    syncInstrumentDefaults(false);
    calculateRisk();
})();
