export function setCookie(name: string, value: string, days = 7) {
  if (typeof window === "undefined") return;
  const expires = new Date();
  expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
  localStorage.setItem(name, value);
}

export function getCookie(name: string): string | null {
  if (typeof window === "undefined") return null;
  return localStorage.getItem(name);
}

export function removeCookie(name: string) {
  if (typeof window === "undefined") return;
  localStorage.removeItem(name);
}
