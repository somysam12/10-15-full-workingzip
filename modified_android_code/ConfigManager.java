package com.silentpanel.app;

import android.content.Context;
import org.json.JSONArray;
import org.json.JSONObject;
import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.ArrayList;
import java.util.List;

public class ConfigManager {

    private static final String CONFIG_URL = "https://your-backend-api.com/api/config";
    private static ConfigCallback callback;

    public interface ConfigCallback {
        void onConfigLoaded(Config config);
        void onConfigFailed(String error);
    }

    public static void fetchConfig(Context context, ConfigCallback cb) {
        callback = cb;
        new Thread(new Runnable() {
            @Override
            public void run() {
                try {
                    URL url = new URL(CONFIG_URL);
                    HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                    conn.setRequestMethod("GET");
                    conn.setConnectTimeout(10000);
                    conn.setReadTimeout(10000);
                    conn.setRequestProperty("Accept", "application/json");

                    int responseCode = conn.getResponseCode();
                    if (responseCode == 200) {
                        BufferedReader reader = new BufferedReader(
                                new InputStreamReader(conn.getInputStream())
                        );
                        StringBuilder response = new StringBuilder();
                        String line;
                        while ((line = reader.readLine()) != null) {
                            response.append(line);
                        }
                        reader.close();

                        Config config = parseConfig(response.toString());
                        if (callback != null) {
                            callback.onConfigLoaded(config);
                        }
                    } else {
                        if (callback != null) {
                            callback.onConfigFailed("HTTP " + responseCode);
                        }
                    }
                    conn.disconnect();
                } catch (Exception e) {
                    if (callback != null) {
                        callback.onConfigFailed(e.getMessage());
                    }
                }
            }
        }).start();
    }

    private static Config parseConfig(String json) throws Exception {
        JSONObject obj = new JSONObject(json);
        
        Config config = new Config();
        config.setServerEnabled(obj.optBoolean("enabled", true));
        config.setAnnouncement(obj.optString("announcement", ""));
        config.setLogoUrl(obj.optString("logo_url", ""));
        config.setAppTitle(obj.optString("app_title", "Silent Panel"));

        JSONArray panelsArray = obj.optJSONArray("panels");
        if (panelsArray != null) {
            List<Panel> panels = new ArrayList<>();
            for (int i = 0; i < panelsArray.length(); i++) {
                JSONObject panelObj = panelsArray.getJSONObject(i);
                Panel panel = new Panel();
                panel.setName(panelObj.getString("name"));
                panel.setUrl(panelObj.getString("url"));
                panel.setSiteKey(panelObj.getString("site_key"));
                panels.add(panel);
            }
            config.setPanels(panels);
        }

        return config;
    }

    public static void updateConfigUrl(String newUrl) {
        // This allows changing the API endpoint at runtime if needed
    }
}
