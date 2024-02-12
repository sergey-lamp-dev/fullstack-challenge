import { defineStore } from "pinia";
import type { WeatherData } from "@/types/weather.interface";
import { getWeatherData } from '@/services/weatherService';

export const useWeatherStore = defineStore("weather", {
  state: (): { weatherData: WeatherData | null } => ({
    weatherData: null,
  }),

  actions: {
    async fetchWeatherData(userId: number): Promise<void> {
      const fetchedWeatherData = await getWeatherData(userId);

      this.weatherData = fetchedWeatherData;
    },
  },
});
