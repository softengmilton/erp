<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";

const props = defineProps({
  chartData: {
    type: Object,
    default: () => ({
      months: [],
      data: []
    })
  }
});

// Responsive state
const screenSize = ref<'mobile' | 'tablet' | 'desktop'>('desktop');
const chartHeight = ref(360);

// Detect screen size changes
const updateScreenSize = () => {
  const width = window.innerWidth;
  if (width < 640) {
    screenSize.value = 'mobile';
    chartHeight.value = 300;
  } else if (width < 1024) {
    screenSize.value = 'tablet';
    chartHeight.value = 320;
  } else {
    screenSize.value = 'desktop';
    chartHeight.value = 360;
  }
};

// Watch screen resize
onMounted(() => {
  updateScreenSize();
  window.addEventListener('resize', updateScreenSize);
});
onUnmounted(() => {
  window.removeEventListener('resize', updateScreenSize);
});

// ✅ Use correct structure for series
const series = computed(() => {
  if (!props.chartData || !Array.isArray(props.chartData.data)) return [];
  return props.chartData.data.map((item: any) => ({
    name: item.name,
    data: item.data
  }));
});

// ✅ Dynamic chart options
const options = computed(() => {
  const isMobile = screenSize.value === 'mobile';

  return {
    chart: {
      type: "bar",
      stacked: true,
      toolbar: { show: !isMobile },
      zoom: { enabled: false },
      fontFamily: 'inherit',
    },
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: isMobile ? "70%" : "55%",
        endingShape: "rounded",
      },
    },
    stroke: {
      show: true,
      width: 2,
      colors: ["transparent"],
    },
    // ✅ Use backend months dynamically
    xaxis: {
      categories: props.chartData?.months?.length
        ? props.chartData.months
        : ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
      labels: {
        style: {
          fontSize: isMobile ? '10px' : '12px',
          fontFamily: 'inherit',
        },
        rotate: isMobile ? -45 : 0,
      },
    },
    yaxis: {
      title: {
        text: "Sales Amount (৳)",
        style: {
          fontSize: isMobile ? '11px' : '12px',
          fontFamily: 'inherit',
        },
      },
      labels: {
        style: {
          fontSize: isMobile ? '10px' : '12px',
          fontFamily: 'inherit',
        },
        formatter: function (val: number) {
          if (isMobile && val >= 1000) {
            return '৳' + (val / 1000).toFixed(0) + 'K';
          }
          return '৳' + val;
        },
      },
    },
    fill: {
      opacity: 1,
    },
    legend: {
      position: isMobile ? "bottom" : "top",
      horizontalAlign: isMobile ? "center" : "left",
      fontSize: isMobile ? '11px' : '12px',
      itemMargin: {
        horizontal: isMobile ? 8 : 12,
        vertical: isMobile ? 4 : 8,
      },
      markers: {
        width: isMobile ? 10 : 12,
        height: isMobile ? 10 : 12,
        radius: isMobile ? 4 : 6,
      },
    },
    tooltip: {
      y: {
        formatter: function (val: number) {
          return "৳" + val.toLocaleString();
        },
      },
      style: {
        fontSize: isMobile ? '12px' : '14px',
        fontFamily: 'inherit',
      },
    },
    grid: {
      borderColor: '#e5e7eb',
      strokeDashArray: isMobile ? 2 : 4,
      xaxis: {
        lines: { show: false },
      },
      yaxis: {
        lines: { show: true },
      },
    },
    theme: {
      mode: document.documentElement.classList.contains("dark") ? "dark" : "light",
    },
    dataLabels: {
      enabled: false,
    },
    states: {
      hover: {
        filter: { type: 'lighten', value: 0.1 },
      },
    },
  };
});
</script>

<template>
  <div class="p-3 sm:p-4">
    <h2 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4 px-2 sm:px-0 text-center sm:text-left">
      Sales by Product Category
    </h2>

    <div class="relative">
      <apexchart
        type="bar"
        :height="chartHeight"
        :options="options"
        :series="series"
        class="w-full"
      />

      <!-- Loading / Empty state -->
      <div
        v-if="series.length === 0"
        class="absolute inset-0 flex items-center justify-center bg-white dark:bg-gray-800 bg-opacity-90 dark:bg-opacity-90"
      >
        <div class="text-center p-4">
          <div class="text-gray-500 dark:text-gray-400 text-sm sm:text-base">
            No sales data available
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
:deep(.apexcharts-canvas) {
  width: 100% !important;
}

/* Mobile chart tweaks */
@media (max-width: 640px) {
  :deep(.apexcharts-menu) {
    padding: 8px;
  }

  :deep(.apexcharts-menu-item) {
    padding: 10px 12px;
    font-size: 14px;
  }

  :deep(.apexcharts-tooltip) {
    font-size: 12px;
    padding: 8px 12px;
  }
}

/* Dark mode adjustments */
:deep(.apexcharts-tooltip.apexcharts-theme-dark) {
  background: #374151;
  color: #fff;
  border: 1px solid #4B5563;
}

:deep(.apexcharts-tooltip-title.apexcharts-theme-dark) {
  background: #4B5563;
  border-bottom: 1px solid #6B7280;
}
</style>
