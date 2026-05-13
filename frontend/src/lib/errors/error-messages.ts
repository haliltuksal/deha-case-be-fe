import type { ApiErrorCode } from '@/types/api';

const FALLBACK_MESSAGE = 'Beklenmeyen bir hata oluştu. Lütfen tekrar deneyin.';

const ERROR_MESSAGES: Record<ApiErrorCode, string> = {
  ERR_VALIDATION: 'Lütfen formdaki hataları düzeltin.',
  ERR_INVALID_CREDENTIALS: 'E-posta veya şifre hatalı.',
  ERR_UNAUTHENTICATED: 'Oturum açmanız gerekiyor.',
  ERR_UNAUTHORIZED: 'Bu işlemi gerçekleştirmek için yetkiniz yok.',
  ERR_NOT_FOUND: 'Aradığınız kayıt bulunamadı.',
  ERR_METHOD_NOT_ALLOWED: 'Geçersiz istek yöntemi.',
  ERR_TOO_MANY_REQUESTS: 'Çok fazla deneme yaptınız. Lütfen kısa bir süre sonra tekrar deneyin.',
  ERR_INSUFFICIENT_STOCK: 'Yeterli stok bulunmuyor.',
  ERR_EMPTY_CART: 'Sepetiniz boş. Sipariş oluşturmak için önce ürün ekleyin.',
  ERR_INVALID_ORDER_TRANSITION: 'Bu sipariş için bu işlem geçerli değil.',
  ERR_TOKEN_EXPIRED: 'Oturumunuz sona erdi. Lütfen tekrar giriş yapın.',
  ERR_TOKEN_BLACKLISTED: 'Oturumunuz sonlandırıldı. Lütfen tekrar giriş yapın.',
  ERR_TOKEN_INVALID: 'Oturum bilgileriniz geçersiz. Lütfen tekrar giriş yapın.',
  ERR_TOKEN_ABSENT: 'Oturum açmanız gerekiyor.',
  ERR_EXCHANGE_PROVIDER_FAILED:
    'Döviz kuru servisine ulaşılamadı. Lütfen biraz sonra tekrar deneyin.',
  ERR_EXCHANGE_RATE_UNAVAILABLE:
    'Şu anda güncel döviz kuru bulunmuyor. Lütfen biraz sonra tekrar deneyin.',
  ERR_HTTP: 'Sunucuyla iletişim kurulamadı. Lütfen tekrar deneyin.',
  ERR_INTERNAL: FALLBACK_MESSAGE,
};

export function getErrorMessage(code: string | undefined, fallback?: string): string {
  if (code && code in ERROR_MESSAGES) {
    return ERROR_MESSAGES[code as ApiErrorCode];
  }
  return fallback ?? FALLBACK_MESSAGE;
}

export const __ERROR_MESSAGES_FOR_TEST__ = ERROR_MESSAGES;
