fetch("chart-data.php")
  .then((res) => res.json())
  .then((data) => {
    const ctx = document.getElementById("keuanganChart").getContext("2d");
    new Chart(ctx, {
      type: "bar",
      data: {
        labels: data.bulan,
        datasets: [
          {
            label: "Uang Masuk",
            backgroundColor: "#3e8ff8",
            data: data.masuk,
          },
          {
            label: "Uang Keluar",
            backgroundColor: "#f66564",
            data: data.keluar,
          },
        ],
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
          },
        },
      },
    });
  });
