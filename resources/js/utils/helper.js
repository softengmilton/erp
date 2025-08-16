const formatCurrency = (value) => {
  if (value === null || value === undefined || isNaN(value)) return 'N/A';
  return `$${parseFloat(value).toFixed(2)}`;
};

// Date formatting function
const formatDate = (dateString) => {
  const date = new Date(dateString);
  const day = date.getDate().toString().padStart(2, '0');
  const month = (date.getMonth() + 1).toString().padStart(2, '0'); // Months are 0-based
  const year = date.getFullYear();
  return `${day}.${month}.${year}`;
};

const formatCompactPriceHistory = (priceMeta) => {
  if (!priceMeta) return "No changes";
  
  try {
    let history = [];
    let current = typeof priceMeta === 'string' ? JSON.parse(priceMeta) : priceMeta;
    
    while (current) {
      history.push(`modified ${current.new_price} x ${current.quantity_sold}x`);
      current = current.previous ? 
        (typeof current.previous === 'string' ? JSON.parse(current.previous) : current.previous) : 
        null;
    }
    
    return history.join('<br>');
    
  } catch (e) {
    console.error("Error parsing price meta:", e);
    return "Invalid data";
  }
}
export { formatCurrency, formatDate,formatCompactPriceHistory };
