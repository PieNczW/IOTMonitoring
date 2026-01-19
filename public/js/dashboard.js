let myChart;

// === 1. DARK MODE & UTILS (Tetap) ===
function toggleTheme() {
    const body = document.body;
    const icon = document.getElementById("theme-icon");
    if (body.getAttribute("data-theme") === "dark") {
        body.removeAttribute("data-theme");
        icon.classList.remove("fa-sun");
        icon.classList.add("fa-moon");
        localStorage.setItem("theme", "light");
    } else {
        body.setAttribute("data-theme", "dark");
        icon.classList.remove("fa-moon");
        icon.classList.add("fa-sun");
        localStorage.setItem("theme", "dark");
    }
}

function convertTemp(celsius, unit) {
    let val = parseFloat(celsius);
    if (unit === "R") return (val * 0.8).toFixed(1);
    if (unit === "F") return ((val * 9) / 5 + 32).toFixed(1);
    if (unit === "K") return (val + 273.15).toFixed(1);
    return val.toFixed(1);
}

// === 2. GANTI SATUAN (Tetap) ===
function setUnit(unit) {
    localStorage.setItem("tempUnit", unit);
    let label = "°C";
    if (unit === "R") label = "°R";
    if (unit === "F") label = "°F";
    if (unit === "K") label = "K";

    document
        .querySelectorAll(".unit-label")
        .forEach((el) => (el.innerText = label));

    const card22 = document.getElementById("card-t22");
    const card11 = document.getElementById("card-t11");
    if (card22)
        card22.innerText = convertTemp(card22.getAttribute("data-val"), unit);
    if (card11)
        card11.innerText = convertTemp(card11.getAttribute("data-val"), unit);

    document.querySelectorAll(".temp-data").forEach((el) => {
        el.innerText = convertTemp(el.getAttribute("data-val"), unit);
    });

    if (window.rawT22 && window.rawT11 && myChart) {
        const newData22 = window.rawT22.map((val) => convertTemp(val, unit));
        const newData11 = window.rawT11.map((val) => convertTemp(val, unit));
        myChart.data.datasets[0].data = newData22;
        myChart.data.datasets[1].data = newData11;

        myChart.options.scales.y.title.text = `Suhu (${label})`;
        myChart.options.plugins.tooltip.callbacks.label = function (context) {
            let val = context.parsed.y;
            if (context.datasetIndex === 2) {
                return context.dataset.label + ": " + val + " PPM";
            } else {
                return context.dataset.label + ": " + val + " " + label;
            }
        };
        myChart.update();
    }

    document
        .querySelectorAll(".unit-btn-group .btn")
        .forEach((btn) => btn.classList.remove("active"));
    document.getElementById(`btn-${unit}`).classList.add("active");
}

// === 3. LOGIKA FILTER PINTAR (YANG DIPERBAIKI) ===
function toggleDataset(index) {
    if (!myChart) return;

    // Cek status saat ini
    const s0 = myChart.isDatasetVisible(0); // DHT22
    const s1 = myChart.isDatasetVisible(1); // DHT11
    const s2 = myChart.isDatasetVisible(2); // Gas
    const isAllVisible = s0 && s1 && s2;

    // === SKENARIO 1: KLIK TOMBOL "SEMUA" ===
    if (index === "all") {
        [0, 1, 2].forEach((i) => myChart.setDatasetVisibility(i, true));
    }

    // === SKENARIO 2: KLIK TOMBOL SENSOR (INDIVIDUAL) ===
    else {
        // Jika saat ini "Semua" nyala, dan user klik satu sensor:
        // Artinya user ingin ISOLASI sensor tersebut (Mode Fokus)
        if (isAllVisible) {
            // Matikan semua dulu
            [0, 1, 2].forEach((i) => myChart.setDatasetVisibility(i, false));
            // Nyalakan HANYA yang diklik
            myChart.setDatasetVisibility(index, true);
        }
        // Jika saat ini mode custom (tidak semua nyala):
        // Artinya user ingin TOGGLE (Nyalakan/Matikan) sensor tersebut
        else {
            const isVisible = myChart.isDatasetVisible(index);
            myChart.setDatasetVisibility(index, !isVisible);
        }
    }

    // === SAFETY CHECK: JANGAN BIARKAN GRAFIK KOSONG ===
    // Kalau user mematikan grafik terakhir, paksa nyalakan lagi
    if (
        !myChart.isDatasetVisible(0) &&
        !myChart.isDatasetVisible(1) &&
        !myChart.isDatasetVisible(2)
    ) {
        myChart.setDatasetVisibility(index === "all" ? 0 : index, true);
    }

    // === SIMPAN KE MEMORI (PERSISTENT) ===
    const visibilityState = [
        myChart.isDatasetVisible(0),
        myChart.isDatasetVisible(1),
        myChart.isDatasetVisible(2),
    ];
    localStorage.setItem("chartState", JSON.stringify(visibilityState));

    // Update Tampilan Tombol & Grafik
    updateFilterButtons();
    myChart.update();
}

// Fungsi Update Visual Tombol
function updateFilterButtons() {
    const s0 = myChart.isDatasetVisible(0);
    const s1 = myChart.isDatasetVisible(1);
    const s2 = myChart.isDatasetVisible(2);

    // Reset semua tombol
    document
        .querySelectorAll(".filter-btn")
        .forEach((btn) => btn.classList.remove("active"));

    // LOGIKA VISUAL:
    // Jika ketiganya nyala -> Aktifkan tombol "Semua" SAJA
    if (s0 && s1 && s2) {
        document.getElementById("filter-all").classList.add("active");
    }
    // Jika tidak -> Aktifkan tombol individu yang nyala
    else {
        if (s0) document.getElementById("filter-dht22").classList.add("active");
        if (s1) document.getElementById("filter-dht11").classList.add("active");
        if (s2) document.getElementById("filter-mq135").classList.add("active");
    }
}

// === 4. INIT UTAMA (Tetap) ===
function initDashboard(labels, t22, t11, gas, lastPpm) {
    window.rawT22 = t22;
    window.rawT11 = t11;

    if (localStorage.getItem("theme") === "dark") {
        document.body.setAttribute("data-theme", "dark");
        document
            .getElementById("theme-icon")
            .classList.replace("fa-moon", "fa-sun");
    }

    // Gauge Chart
    const ctxGauge = document.getElementById("gaugeChart").getContext("2d");
    let gaugeColor = "#198754";
    if (lastPpm > 100) gaugeColor = "#ffc107";
    if (lastPpm > 300) gaugeColor = "#dc3545";

    new Chart(ctxGauge, {
        type: "doughnut",
        data: {
            labels: ["Gas", "Sisa"],
            datasets: [
                {
                    data: [lastPpm, 1000 - lastPpm],
                    backgroundColor: [gaugeColor, "#e9ecef"],
                    borderWidth: 0,
                    cutout: "75%",
                },
            ],
        },
        options: {
            rotation: -90,
            circumference: 180,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false },
            },
        },
    });

    // Main Chart
    const ctx = document.getElementById("sensorChart").getContext("2d");

    let gradientBlue = ctx.createLinearGradient(0, 0, 0, 400);
    gradientBlue.addColorStop(0, "rgba(13, 110, 253, 0.5)");
    gradientBlue.addColorStop(1, "rgba(13, 110, 253, 0.0)");

    let gradientRed = ctx.createLinearGradient(0, 0, 0, 400);
    gradientRed.addColorStop(0, "rgba(220, 53, 69, 0.5)");
    gradientRed.addColorStop(1, "rgba(220, 53, 69, 0.0)");

    let currentUnit = localStorage.getItem("tempUnit") || "C";
    let unitLabel = "°C";
    if (currentUnit === "R") unitLabel = "°R";
    if (currentUnit === "F") unitLabel = "°F";
    if (currentUnit === "K") unitLabel = "K";

    myChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: labels,
            datasets: [
                {
                    label: "Suhu DHT22",
                    data: [...t22],
                    borderColor: "#0d6efd",
                    backgroundColor: gradientBlue,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    yAxisID: "y",
                },
                {
                    label: "Suhu DHT11",
                    data: [...t11],
                    borderColor: "#0dcaf0",
                    borderDash: [5, 5],
                    borderWidth: 2,
                    tension: 0.4,
                    yAxisID: "y",
                },
                {
                    label: "Gas MQ135",
                    data: gas,
                    borderColor: "#dc3545",
                    backgroundColor: gradientRed,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    yAxisID: "y1",
                },
            ],
        },
        options: {
            responsive: true,
            interaction: { mode: "index", intersect: false },
            plugins: {
                legend: {
                    position: "top",
                    labels: { usePointStyle: true, boxWidth: 6 },
                },
                tooltip: {
                    backgroundColor: "rgba(0,0,0,0.8)",
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || "";
                            if (label) {
                                label += ": ";
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y;
                                if (context.datasetIndex === 2) {
                                    label += " PPM";
                                } else {
                                    label += " " + unitLabel;
                                }
                            }
                            return label;
                        },
                    },
                },
            },
            scales: {
                y: {
                    type: "linear",
                    display: true,
                    position: "left",
                    title: {
                        display: true,
                        text: `Suhu (${unitLabel})`,
                        font: { weight: "bold" },
                    },
                },
                y1: {
                    type: "linear",
                    display: true,
                    position: "right",
                    grid: { drawOnChartArea: false },
                    title: {
                        display: true,
                        text: "Gas (PPM)",
                        font: { weight: "bold" },
                    },
                },
            },
        },
    });

    // === RESTORE FILTER STATE ===
    const savedState = localStorage.getItem("chartState");
    if (savedState) {
        const visibility = JSON.parse(savedState);
        visibility.forEach((isVisible, index) => {
            myChart.setDatasetVisibility(index, isVisible);
        });
    }

    setUnit(currentUnit);
    updateFilterButtons(); // Jalankan update visual tombol saat pertama load
}

function toggleInputs() {
    const period = document.getElementById("periodSelect").value;
    const form = document.getElementById("filterForm");

    // Sembunyikan semua input dulu
    document.getElementById("dateWrapper").style.display = "none";
    document.getElementById("weekWrapper").style.display = "none";
    document.getElementById("monthWrapper").style.display = "none";

    // Reset value input agar tidak bentrok saat ganti mode
    // Kecuali jika ini adalah load awal (kita cek dari PHP variables nanti di controller)

    if (period === "today") {
        // Jika pilih Hari Ini, langsung submit untuk reset
        window.location.href = "{{ url('/dashboard') }}";
    } else if (period === "custom_date") {
        document.getElementById("dateWrapper").style.display = "block";
    } else if (period === "week") {
        document.getElementById("weekWrapper").style.display = "block";
    } else if (period === "month") {
        document.getElementById("monthWrapper").style.display = "block";
    }
}

// Jalankan saat halaman loading agar input tetap muncul jika sedang difilter
window.addEventListener("DOMContentLoaded", (event) => {
    const currentPeriod = "{{ $period }}";

    if (currentPeriod === "custom_date") {
        document.getElementById("dateWrapper").style.display = "block";
    } else if (currentPeriod === "week") {
        document.getElementById("weekWrapper").style.display = "block";
    } else if (currentPeriod === "month") {
        document.getElementById("monthWrapper").style.display = "block";
    }
});

