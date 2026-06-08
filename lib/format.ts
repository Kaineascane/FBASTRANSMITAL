export function formatTransmittalDate(date: string): string {
  const d = new Date(date + 'T12:00:00');
  return d
    .toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
    .toUpperCase();
}
