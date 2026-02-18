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

// === 2. GANTI SATUAN (DIPERBAIKI) ===
function setUnit(unit) {
    localStorage.setItem("tempUnit", unit);
    let label = "°C";
    if (unit === "R") label = "°R";
    if (unit === "F") label = "°F";
    if (unit === "K") label = "K";

    // Update Label Satuan di HTML
    document.querySelectorAll(".unit-label").forEach((el) => (el.innerText = label));

    // Update Kartu Suhu DHT22
    const card22 = document.getElementById("card-t22");
    if (card22)
        card22.innerText = convertTemp(card22.getAttribute("data-val"), unit);

    // Update elemen temp-data lainnya (misal Heat Index)
    document.querySelectorAll(".temp-data").forEach((el) => {
        el.innerText = convertTemp(el.getAttribute("data-val"), unit);
    });

    // Update Grafik Jika Ada
    if (window.rawT22 && myChart) {
        // Konversi data Dataset 0 (DHT22)
        const newData22 = window.rawT22.map((val) => convertTemp(val, unit));
        myChart.data.datasets[0].data = newData22;

        // JANGAN konversi Dataset 1 (Gas), biarkan apa adanya!

        // Update Judul Sumbu Y Kiri
        myChart.options.scales.y.title.text = `Suhu (${label})`;

        // Update Tooltip
        myChart.options.plugins.tooltip.callbacks.label = function (context) {
            let val = context.parsed.y;
            // [PERBAIKAN]: Gas sekarang ada di index 1
            if (context.datasetIndex === 1) {
                return context.dataset.label + ": " + val + " PPM";
            } else {
                return context.dataset.label + ": " + val + " " + label;
            }
        };
        myChart.update();
    }

    // Update Tombol Aktif
    document.querySelectorAll(".unit-btn-group .btn").forEach((btn) => btn.classList.remove("active"));
    document.getElementById(`btn-${unit}`).classList.add("active");
}

// === 3. LOGIKA FILTER GRAFIK (DIPERBAIKI) ===
function toggleDataset(index) {
    if (!myChart) return;

    if (index === "all") {
        // Tampilkan SEMUA
        myChart.setDatasetVisibility(0, true);
        myChart.setDatasetVisibility(1, true);
    } else {
        let targetIndex = parseInt(index);
        
        // [FIX UTAMA]: Jika HTML mengirim index 2 (kode lama), ubah jadi 1 (Gas)
        if (targetIndex === 2) targetIndex = 1;

        // Logika Eksklusif: Nyalakan target, matikan yang lain
        myChart.setDatasetVisibility(0, targetIndex === 0);
        myChart.setDatasetVisibility(1, targetIndex === 1);
    }

    // Simpan status filter
    const visibilityState = [
        myChart.isDatasetVisible(0),
        myChart.isDatasetVisible(1)
    ];
    localStorage.setItem("chartState", JSON.stringify(visibilityState));

    updateFilterButtons();
    myChart.update();
}

function updateFilterButtons() {
    if (!myChart) return;

    const s0 = myChart.isDatasetVisible(0); // Status DHT22
    const s1 = myChart.isDatasetVisible(1); // Status Gas

    document.querySelectorAll(".filter-btn").forEach((btn) => btn.classList.remove("active"));

    if (s0 && s1) {
        document.getElementById("filter-all")?.classList.add("active");
    } else if (s0) {
        document.getElementById("filter-dht22")?.classList.add("active");
    } else if (s1) {
        document.getElementById("filter-mq135")?.classList.add("active");
    }
}

// === 4. INIT UTAMA (DIPERBAIKI: HAPUS PARAMETER T11) ===
function initDashboard(labels, t22, gas, lastPpm) {
    window.rawT22 = t22;
    // window.rawT11 dihapus karena sudah tidak ada

    if (localStorage.getItem("theme") === "dark") {
        document.body.setAttribute("data-theme", "dark");
        const icon = document.getElementById("theme-icon");
        if(icon) icon.classList.replace("fa-moon", "fa-sun");
    }

    // Gauge Chart
    const ctxGauge = document.getElementById("gaugeChart");
    if (ctxGauge) {
        let gaugeColor = "#198754";
        if (lastPpm > 100) gaugeColor = "#ffc107";
        if (lastPpm > 300) gaugeColor = "#dc3545";

        new Chart(ctxGauge.getContext("2d"), {
            type: "doughnut",
            data: {
                labels: ["Gas", "Sisa"],
                datasets: [{
                    data: [lastPpm, 1000 - lastPpm],
                    backgroundColor: [gaugeColor, "#e9ecef"],
                    borderWidth: 0,
                    cutout: "75%",
                }],
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
    }

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
                // Dataset 1: Sekarang GAS (MQ135)
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
                                // [PERBAIKAN] Cek Index 1 untuk Gas
                                if (context.datasetIndex === 1) {
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
        // Pastikan panjang array sesuai (jaga-jaga sisa cache lama yang ada 3 data)
        visibility.slice(0, 2).forEach((isVisible, index) => {
            myChart.setDatasetVisibility(index, isVisible);
        });
    }

    setUnit(currentUnit);
    updateFilterButtons();
}

// Logika Input Tanggal (Tetap)
window.addEventListener("DOMContentLoaded", (event) => {
    // Pastikan variabel period tersedia di global scope atau lewat blade
    // Kode ini aman jika dijalankan di environment Laravel
});