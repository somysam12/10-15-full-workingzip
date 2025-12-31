package com.silentpanel.app;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;

public class WebsiteSelectorActivity extends Activity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_selector);

        // ===== ORIGINAL BINDS (UNCHANGED) =====
        bind(R.id.btn1, "https://silentmultipanel.vippanel.in", "silent");
        bind(R.id.btn2, "https://loadervip.in/api/kingandroid/public", "kingandroid");
        bind(R.id.btn3, "https://loadervip.in/api/fraction/public", "fraction");
        bind(R.id.btn4, "https://globalvipkeys.in/kingandroid/public", "kingglobal");
        bind(R.id.btn5, "https://vipowner.online/FIREX", "firex");
        bind(R.id.btn6, "https://vipowner.online/Crozn", "crozn");
        bind(R.id.btn7, "https://saurabh.panle.shop", "dulux");
        bind(R.id.btn8, "https://bgmicheat.vipsververrpanel.xyz", "bgmi");
        bind(R.id.btn9, "https://frozenfire.shop", "frozen");
        bind(R.id.btn10, "https://beastcrown.vippanel.online", "beast");
    }

    // ===== ORIGINAL METHOD (LOGIC UNCHANGED) =====
    private void bind(int id, final String url, final String siteKey) {
        Button b = findViewById(id);

        // ✅ NEW: safety check (compatibility fix, no logic change)
        if (b == null) return;

        b.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent i = new Intent(WebsiteSelectorActivity.this, MainActivity.class);
                i.putExtra("load_url", url);
                i.putExtra("site_key", siteKey); // already in your code
                startActivity(i);
            }
        });
    }
}