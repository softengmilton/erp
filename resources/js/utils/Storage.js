// src/utils/Storage.js

const Storage = {
  // Save item
  set(key, value) {
    if (!key) return;
    try {
      const data = typeof value === "string" ? value : JSON.stringify(value);
      localStorage.setItem(key, data);
    } catch (err) {
      console.error("Error saving to localStorage", err);
    }
  },

  // Get item
  get(key) {
    if (!key) return null;
    try {
      const data = localStorage.getItem(key);
      return data ? JSON.parse(data) : null;
    } catch {
      // If parsing fails, return raw string
      return localStorage.getItem(key);
    }
  },

  // Remove item
  remove(key) {
    if (!key) return;
    localStorage.removeItem(key);
  },

  // Clear all storage
  clear() {
    localStorage.clear();
  },
};

export default Storage;
