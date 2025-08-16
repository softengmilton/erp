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
   function formatAdjustmentHistory(adjustmentMeta) {
    // Parse if it's a JSON string
    const data = typeof adjustmentMeta === 'string' 
        ? JSON.parse(adjustmentMeta) 
        : adjustmentMeta;

    if (!data) return "No adjustments";

    const lines = [];

    // Format current adjustment if exists
    if (data.current && data.current.type) {
        lines.push(formatAdjustmentLine(data.current));
    }

    // Format historical adjustments
    if (Array.isArray(data.history)) {
        data.history.forEach(adjustment => {
            if (adjustment && adjustment.type) {
                lines.push(formatAdjustmentLine(adjustment));
            }
        });
    }

    return lines.length > 0 
        ? lines.join('<br>') 
        : "No adjustments";
}

function formatAdjustmentLine(adjustment) {
    const date = new Date(adjustment.adjusted_at).toLocaleDateString();
    const type = adjustment.type === 'damage' ? 'Damaged' : 'Returned';
    const quantity = Math.abs(adjustment.quantity);
    const note = adjustment.note ? ` (${adjustment.note})` : '';

    return `${date} - ${type} ${quantity} items${note}`;
}
export { formatCurrency, formatDate,formatCompactPriceHistory,formatAdjustmentHistory };
