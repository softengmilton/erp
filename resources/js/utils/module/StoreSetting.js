// src/utils/module/StoreSetting.js
import { reactive, computed } from "vue";
import { usePage } from "@inertiajs/vue3";

const state = reactive({
  settings: {}, // start empty
});

// Call this from your component setup() to initialize
export function initStoreSetting() {
  const page = usePage();
  if (page && page.props && page.props.storeSettings) {
    state.settings = page.props.storeSettings;
  }
}

export default {
  state,

  get(key, fallback = null) {
    return state.settings?.[key] ?? fallback;
  },

  update(key, value) {
    state.settings[key] = value;
  },

  all: computed(() => state.settings),
};
