

<canvas id="myChart"></canvas>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  var ctx = document.getElementById('myChart').getContext('2d');
  var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr'],
      datasets: [{
        label: 'Ventes',
        data: [12, 19, 3, 5],
        backgroundColor: 'rgba(26, 99, 106, 0.83)'
      }]
    }
  });
</script>
