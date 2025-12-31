package com.silentpanel.app;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Intent;
import android.os.Build;
import android.os.Bundle;
import android.view.View;
import android.view.Window;
import android.view.WindowManager;
import android.widget.Button;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;
import java.util.List;

public class WebsiteSelectorActivity extends Activity {

    private LinearLayout buttonsContainer;
    private ProgressBar loadingProgress;
    private TextView announcementText;
    private Config currentConfig;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        requestWindowFeature(Window.FEATURE_NO_TITLE);
        getWindow().setFlags(
                WindowManager.LayoutParams.FLAG_FULLSCREEN,
                WindowManager.LayoutParams.FLAG_FULLSCREEN
        );
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            getWindow().setStatusBarColor(0xFF0A0E27);
        }

        setContentView(R.layout.activity_selector);

        buttonsContainer = findViewById(R.id.buttons_container);
        loadingProgress = findViewById(R.id.loading_progress);
        announcementText = findViewById(R.id.announcement_text);

        loadingProgress.setVisibility(View.VISIBLE);

        // Fetch configuration from server
        ConfigManager.fetchConfig(this, new ConfigManager.ConfigCallback() {
            @Override
            public void onConfigLoaded(Config config) {
                currentConfig = config;
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        loadingProgress.setVisibility(View.GONE);
                        setupUI(config);
                    }
                });
            }

            @Override
            public void onConfigFailed(String error) {
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        loadingProgress.setVisibility(View.GONE);
                        Toast.makeText(WebsiteSelectorActivity.this,
                                "Failed to load config: " + error,
                                Toast.LENGTH_LONG).show();
                        loadDefaultPanels();
                    }
                });
            }
        });
    }

    private void setupUI(Config config) {
        // Check if server is enabled
        if (!config.isServerEnabled()) {
            AlertDialog.Builder builder = new AlertDialog.Builder(this);
            builder.setTitle("Server Offline");
            builder.setMessage("The server is currently offline. The app will not function.");
            builder.setPositiveButton("OK", (dialog, which) -> finish());
            builder.setCancelable(false);
            builder.show();
            return;
        }

        // Show announcement if available
        if (config.getAnnouncement() != null && !config.getAnnouncement().isEmpty()) {
            if (announcementText != null) {
                announcementText.setVisibility(View.VISIBLE);
                announcementText.setText(config.getAnnouncement());
            }
        }

        // Clear existing buttons and create dynamic ones
        if (buttonsContainer != null) {
            buttonsContainer.removeAllViews();
            List<Panel> panels = config.getPanels();

            if (panels != null && !panels.isEmpty()) {
                for (Panel panel : panels) {
                    createButton(panel);
                }
            } else {
                Toast.makeText(this, "No panels configured", Toast.LENGTH_SHORT).show();
            }
        }
    }

    private void createButton(Panel panel) {
        Button button = new Button(this);
        button.setText(panel.getName());
        button.setTextSize(14);
        button.setPadding(16, 16, 16, 16);

        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT,
                LinearLayout.LayoutParams.WRAP_CONTENT
        );
        params.setMargins(16, 8, 16, 8);
        button.setLayoutParams(params);

        button.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(WebsiteSelectorActivity.this, MainActivity.class);
                intent.putExtra("load_url", panel.getUrl());
                intent.putExtra("site_key", panel.getSiteKey());
                startActivity(intent);
            }
        });

        if (buttonsContainer != null) {
            buttonsContainer.addView(button);
        }
    }

    private void loadDefaultPanels() {
        // Fallback to default hardcoded panels if server fails
        Config fallbackConfig = new Config();
        fallbackConfig.setServerEnabled(true);

        List<Panel> defaultPanels = new java.util.ArrayList<>();
        defaultPanels.add(createPanel("Silent Multi Panel", "https://silentmultipanel.vippanel.in", "silent"));
        defaultPanels.add(createPanel("King Android", "https://loadervip.in/api/kingandroid/public", "kingandroid"));
        defaultPanels.add(createPanel("Fraction", "https://loadervip.in/api/fraction/public", "fraction"));
        defaultPanels.add(createPanel("King Global", "https://globalvipkeys.in/kingandroid/public", "kingglobal"));
        defaultPanels.add(createPanel("FireX", "https://vipowner.online/FIREX", "firex"));
        defaultPanels.add(createPanel("Crozn", "https://vipowner.online/Crozn", "crozn"));
        defaultPanels.add(createPanel("Dulux", "https://saurabh.panle.shop", "dulux"));
        defaultPanels.add(createPanel("BGMI", "https://bgmicheat.vipsververrpanel.xyz", "bgmi"));
        defaultPanels.add(createPanel("Frozen Fire", "https://frozenfire.shop", "frozen"));
        defaultPanels.add(createPanel("Beast Crown", "https://beastcrown.vippanel.online", "beast"));

        fallbackConfig.setPanels(defaultPanels);
        setupUI(fallbackConfig);
    }

    private Panel createPanel(String name, String url, String siteKey) {
        Panel panel = new Panel();
        panel.setName(name);
        panel.setUrl(url);
        panel.setSiteKey(siteKey);
        return panel;
    }
}
