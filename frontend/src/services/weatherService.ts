import axios from 'axios';
import type { WeatherData } from '@/types/weatherData.interface';

const API_BASE_URL = 'http://localhost';

export const getWeatherData = async (userId: number): Promise<WeatherData> => {
  try {
    const response = await axios.get(`${API_BASE_URL}/users/${userId}/weather`);
    return response.data;
  } catch (error) {
    console.error(error);
    return [];
  }
};
