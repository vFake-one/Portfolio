/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — MAIN FRONTEND INTERACTION SCRIPT
 * ============================================================================
 * File: assets/js/main.js
 *
 * Handles:
 * 1. Active navigation link highlighting on scroll (IntersectionObserver)
 * 2. Mobile navigation menu auto-close on anchor click
 * 3. Client-side usability validation for the Contact & Inquiry form
 * 4. Service card "Inquire" button pre-filling the Contact form Area of Interest
 * 5. Trading Performance Chart.js initialization (only when genuine records exist)
 * ============================================================================
 */

(function () {
    'use strict';

    // 1. Active section indicator for landing page navigation
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.pg-nav-list .nav-link[data-section]');

    if (sections.length > 0 && navLinks.length > 0 && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        const activeId = entry.target.getAttribute('id');
                        navLinks.forEach(function (link) {
                            if (link.getAttribute('data-section') === activeId) {
                                link.classList.add('active');
                            } else {
                                link.classList.remove('active');
                            }
                        });
                    }
                });
            },
            {
                rootMargin: '-25% 0px -60% 0px',
                threshold: 0
            }
        );

        sections.forEach(function (sec) {
            observer.observe(sec);
        });
    }

    // 2. Close responsive mobile navbar when clicking an in-page anchor link
    const navbarCollapse = document.getElementById('primaryNavbar');
    if (navbarCollapse && typeof bootstrap !== 'undefined') {
        const links = navbarCollapse.querySelectorAll('.nav-link, .btn');
        links.forEach(function (link) {
            link.addEventListener('click', function () {
                if (navbarCollapse.classList.contains('show')) {
                    const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                }
            });
        });
    }

    // 3. Pre-select Area of Interest when clicking a Service card Inquiry button
    const serviceInquiryBtns = document.querySelectorAll('[data-service-interest]');
    const interestSelect = document.getElementById('contactAreaOfInterest');
    serviceInquiryBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const serviceName = btn.getAttribute('data-service-interest');
            if (interestSelect && serviceName) {
                let matched = false;
                Array.from(interestSelect.options).forEach(function (opt) {
                    if (opt.value === serviceName) {
                        opt.selected = true;
                        matched = true;
                    }
                });
                if (!matched) {
                    const customOpt = document.createElement('option');
                    customOpt.value = serviceName;
                    customOpt.textContent = serviceName;
                    customOpt.selected = true;
                    interestSelect.appendChild(customOpt);
                }
            }
        });
    });

    // 4. Client-side Contact & Inquiry Form usability validation
    const contactForm = document.getElementById('contactInquiryForm');
    if (contactForm) {
        const messageField = document.getElementById('contactMessage');
        const charCounter = document.getElementById('contactMessageCounter');

        if (messageField && charCounter) {
            const updateCount = function () {
                const len = messageField.value.trim().length;
                charCounter.textContent = `${len} / 3000 characters (minimum 15)`;
            };
            messageField.addEventListener('input', updateCount);
            updateCount();
        }

        contactForm.addEventListener('submit', function (event) {
            const fullName = document.getElementById('contactFullName');
            const email = document.getElementById('contactEmail');
            const interest = document.getElementById('contactAreaOfInterest');
            const message = document.getElementById('contactMessage');
            const consent = document.getElementById('contactPrivacyConsent');
            const clientErrorBox = document.getElementById('contactClientErrors');

            const errors = [];

            if (!fullName || fullName.value.trim().length < 2) {
                errors.push('Please enter your full name (at least 2 characters).');
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email || !emailRegex.test(email.value.trim())) {
                errors.push('Please enter a valid email address.');
            }

            if (!interest || interest.value.trim() === '') {
                errors.push('Please select an area of interest.');
            }

            if (!message || message.value.trim().length < 15) {
                errors.push('Please write a message of at least 15 characters.');
            }

            if (!consent || !consent.checked) {
                errors.push('Please check the privacy notice consent box before submitting.');
            }

            if (errors.length > 0) {
                event.preventDefault();
                if (clientErrorBox) {
                    clientErrorBox.classList.remove('d-none');
                    clientErrorBox.innerHTML = '<strong>Please review the following:</strong><ul class="mb-0 mt-1">' +
                        errors.map(function (err) { return '<li>' + err + '</li>'; }).join('') +
                        '</ul>';
                    clientErrorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else if (clientErrorBox) {
                clientErrorBox.classList.add('d-none');
            }
        });
    }

    // 5. Initialize Chart.js Trading Performance Charts (ONLY if real records exist in DOM data)
    const chartDataEl = document.getElementById('pgPerformanceChartData');
    if (chartDataEl && typeof Chart !== 'undefined') {
        try {
            const payload = JSON.parse(chartDataEl.textContent || '{}');
            if (payload && payload.hasRecords === true) {
                const currency = payload.currency || 'USD';

                // Chart 1: Historical Equity Curve
                const equityCtx = document.getElementById('equityCurveChart');
                if (equityCtx) {
                    new Chart(equityCtx, {
                        type: 'line',
                        data: {
                            labels: payload.equityLabels || [],
                            datasets: [{
                                label: `Cumulative Realized P/L (${currency})`,
                                data: payload.equityValues || [],
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.14)',
                                borderWidth: 2.5,
                                pointRadius: 3.5,
                                pointBackgroundColor: '#10b981',
                                fill: true,
                                tension: 0.25
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { labels: { color: '#cbd5e1' } }
                            },
                            scales: {
                                x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(148,163,184,0.1)' } },
                                y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(148,163,184,0.1)' } }
                            }
                        }
                    });
                }

                // Chart 2: Monthly Profit & Loss Bar Chart
                const monthlyCtx = document.getElementById('monthlyPlChart');
                if (monthlyCtx) {
                    const monthlyValues = payload.monthlyValues || [];
                    const barColors = monthlyValues.map(function (v) {
                        return v >= 0 ? 'rgba(16, 185, 129, 0.8)' : 'rgba(239, 68, 68, 0.8)';
                    });
                    new Chart(monthlyCtx, {
                        type: 'bar',
                        data: {
                            labels: payload.monthlyLabels || [],
                            datasets: [{
                                label: `Monthly Realized P/L (${currency})`,
                                data: monthlyValues,
                                backgroundColor: barColors,
                                borderRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { labels: { color: '#cbd5e1' } }
                            },
                            scales: {
                                x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(148,163,184,0.1)' } },
                                y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(148,163,184,0.1)' } }
                            }
                        }
                    });
                }

                // Chart 3: Win / Loss / Breakeven Distribution
                const winLossCtx = document.getElementById('winLossChart');
                if (winLossCtx) {
                    new Chart(winLossCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Winning Trades', 'Losing Trades', 'Break-Even Trades'],
                            datasets: [{
                                data: payload.winLossValues || [0, 0, 0],
                                backgroundColor: ['#10b981', '#ef4444', '#64748b'],
                                borderColor: '#131e36',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { color: '#cbd5e1' }
                                }
                            }
                        }
                    });
                }
            }
        } catch (err) {
            console.error('Could not parse performance chart data:', err);
        }
    }
})();
