import type { WeatherData } from '@/types/weather.interface';
export interface User {
  id: number;
  name: string;
  email: string;
  latitude: string;
  longitude: string;
  weather?: WeatherData;
}
