import { describe, expect, it } from 'vitest';
import { formatPrice } from './format';

describe('formatPrice', () => {
  it('formats Turkish lira with the symbol and comma decimal separator', () => {
    const formatted = formatPrice('99.99', 'TRY');
    expect(formatted).toContain('₺');
    expect(formatted).toContain('99,99');
  });

  it('formats US dollars with the symbol and dot decimal separator', () => {
    const formatted = formatPrice('99.99', 'USD');
    expect(formatted).toContain('$');
    expect(formatted).toContain('99.99');
  });

  it('formats euros using the German locale', () => {
    const formatted = formatPrice('1234.50', 'EUR');
    expect(formatted).toContain('€');
    expect(formatted).toContain('1.234,50');
  });

  it('always renders two fractional digits even when the input has none', () => {
    expect(formatPrice('100', 'USD')).toContain('100.00');
    expect(formatPrice('100', 'TRY')).toContain('100,00');
  });

  it('accepts numeric input as well as decimal strings', () => {
    expect(formatPrice(42.5, 'USD')).toContain('42.50');
  });

  it('rejects non-numeric input with a clear error', () => {
    expect(() => formatPrice('not-a-number', 'TRY')).toThrow(/non-numeric/i);
  });
});
