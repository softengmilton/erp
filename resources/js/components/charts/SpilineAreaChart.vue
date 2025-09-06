<script setup lang="ts">
import { ref, computed, watchEffect, onMounted, onUnmounted } from "vue";

const props = defineProps({
  chartData: {
    type: Object,
    default: () => ({
      revenue: [],
      expenses: [],
      months: []
    })
  }
});

// Responsive state
const screenSize = ref<'mobile' | 'tablet' | 'desktop'>('desktop');
const chartHeight = ref(300);

// Detect screen size changes
const updateScreenSize = () => {
  const width = window.innerWidth;
  if (width < 640) {
    screenSize.value = 'mobile';
    chartHeight.value = 250;
  } else if (width < 1024) {
    screenSize.value = 'tablet';
    chartHeight.value = 280;
  } else {
    screenSize.value = 'desktop';
    chartHeight.value = 300;
  }
};

// Set up responsive event listener
onMounted(() => {
  updateScreenSize();
  window.addEventListener('resize', updateScreenSize);
});

onUnmounted(() => {
  window.removeEventListener('resize', updateScreenSize);
});

const series = computed(() => [
  {
    name: "Revenue",
    data: props.chartData.revenue || []
  },
  {
    name: "Expenses",
    data: props.chartData.expenses || []
  }
]);

const options = computed(() => {
  const isMobile = screenSize.value === 'mobile';
  const isTablet = screenSize.value === 'tablet';

  return {
    chart: {
      id: "spline-area",
      toolbar: {
        show: !isMobile,
        tools: {
          download: true,
          selection: false,
          zoom: false,
          zoomin: false,
          zoomout: false,
          pan: false,
          reset: false
        }
      },
      zoom: { enabled: false },
      fontFamily: 'inherit',
      animations: {
        enabled: true,
        easing: 'easeinout',
        speed: 800
      }
    },
    stroke: {
      curve: "smooth",
      width: isMobile ? 2 : 3,
      lineCap: 'round'
    },
    dataLabels: {
      enabled: false
    },
    markers: {
      size: isMobile ? 3 : 4,
      hover: {
        size: isMobile ? 5 : 6,
        sizeOffset: 2
      }
    },
    xaxis: {
      categories: props.chartData.months || [],
      labels: {
        style: {
          fontSize: isMobile ? '10px' : '12px',
          fontFamily: 'inherit',
        },
        rotate: isMobile ? -45 : 0,
        hideOverlappingLabels: true,
        trim: true
      },
      axisBorder: {
        show: false
      },
      axisTicks: {
        show: false
      }
    },
    yaxis: {
      labels: {
        style: {
          fontSize: isMobile ? '10px' : '12px',
          fontFamily: 'inherit',
        },
        formatter: function(val: number) {
          if (isMobile && Math.abs(val) >= 1000) {
            return '$' + (val / 1000).toFixed(1) + 'K';
          }
          return '$' + val;
        }
      }
    },
    fill: {
      type: "gradient",
      gradient: {
        shadeIntensity: 0.6,
        opacityFrom: 0.5,
        opacityTo: 0.1,
        stops: [0, 90, 100],
        gradientToColors: ['#3B82F6', '#EF4444']
      },
    },
    grid: {
      borderColor: "#e5e7eb",
      strokeDashArray: isMobile ? 2 : 4,
      xaxis: {
        lines: {
          show: false
        }
      },
      yaxis: {
        lines: {
          show: true
        }
      }
    },
    legend: {
      position: isMobile ? "bottom" : "top",
      horizontalAlign: isMobile ? "center" : "left",
      fontSize: isMobile ? '11px' : '12px',
      markers: {
        width: isMobile ? 10 : 12,
        height: isMobile ? 10 : 12,
        radius: isMobile ? 4 : 6,
      },
      itemMargin: {
        horizontal: isMobile ? 8 : 12,
        vertical: isMobile ? 4 : 8
      }
    },
    tooltip: {
      shared: true,
      intersect: false,
      y: {
        formatter: function(val: number) {
          return "$" + val.toLocaleString();
        }
      },
      style: {
        fontSize: isMobile ? '12px' : '14px',
        fontFamily: 'inherit',
      },
      marker: {
        show: true
      }
    },
    responsive: [{
      breakpoint: 640,
      options: {
        chart: {
          toolbar: {
            show: false
          }
        },
        legend: {
          position: 'bottom',
          horizontalAlign: 'center',
          fontSize: '11px'
        },
        xaxis: {
          labels: {
            rotate: -45,
            style: {
              fontSize: '10px'
            }
          }
        },
        yaxis: {
          labels: {
            style: {
              fontSize: '10px'
            },
            formatter: function(val: number) {
              if (Math.abs(val) >= 1000) {
                return '$' + (val / 1000).toFixed(1) + 'K';
              }
              return '$' + val;
            }
          }
        },
        stroke: {
          width: 2
        },
        markers: {
          size: 3
        }
      }
    }],
    theme: {
      mode: document.documentElement.classList.contains("dark") ? "dark" : "light",
    }
  };
});

// Update options when chartData changes
watchEffect(() => {
  options.value = {
    ...options.value,
    xaxis: {
      ...options.value.xaxis,
      categories: props.chartData.months || []
    }
  };
});
</script>

<template>
  <div class="p-3 sm:p-4">
    <h2 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4 px-2 sm:px-0 text-center sm:text-left">
      Revenue vs Expenses
    </h2>
    <div class="relative">
      <apexchart
        type="area"
        :height="chartHeight"
        :options="options"
        :series="series"
        class="w-full"
      />

      <!-- Loading/Empty state -->
      <div
        v-if="series[0].data.length === 0 || series[1].data.length === 0"
        class="absolute inset-0 flex items-center justify-center bg-white dark:bg-gray-800 bg-opacity-90 dark:bg-opacity-90 rounded-lg"
      >
        <div class="text-center p-4">
          <div class="text-gray-500 dark:text-gray-400 text-sm sm:text-base">
            No financial data available
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile legend for better visibility -->
    <div v-if="screenSize === 'mobile'" class="mt-2 text-xs text-center text-gray-600 dark:text-gray-400">
      <span class="inline-block w-3 h-3 bg-blue-500 rounded-full mr-1"></span>
      Revenue
      <span class="inline-block w-3 h-3 bg-red-500 rounded-full mx-3 mr-1"></span>
      Expenses
    </div>
  </div>
</template>

<style scoped>
/* Ensure chart container is responsive */
:deep(.apexcharts-canvas) {
  width: 100% !important;
}

/* Improve touch targets on mobile */
@media (max-width: 640px) {
  :deep(.apexcharts-tooltip) {
    font-size: 12px;
    padding: 8px 12px;
  }

  :deep(.apexcharts-legend) {
    padding: 8px 0;
  }
}

/* Smooth transitions for chart elements */
:deep(.apexcharts-area) {
  transition: opacity 0.3s ease;
}

:deep(.apexcharts-series) {
  transition: opacity 0.3s ease;
}

/* Dark mode adjustments */
:deep(.apexcharts-tooltip.apexcharts-theme-dark) {
  background: #374151;
  color: #fff;
  border: 1px solid #4B5563;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

:deep(.apexcharts-tooltip-title.apexcharts-theme-dark) {
  background: #4B5563;
  border-bottom: 1px solid #6B7280;
  font-weight: 600;
}

/* Better focus states for accessibility */
:deep(.apexcharts-legend-marker) {
  transition: transform 0.2s ease;
}

:deep(.apexcharts-legend-marker:hover) {
  transform: scale(1.1);
}
</style>
