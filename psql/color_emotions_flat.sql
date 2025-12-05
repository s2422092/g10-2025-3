-- color_emotions_flat テーブル作成
CREATE TABLE IF NOT EXISTS public.color_emotions_flat (
    id SERIAL PRIMARY KEY,
    color_id INTEGER NOT NULL,
    color_name VARCHAR(100) NOT NULL,   -- 表示名用
    color_code VARCHAR(7) NOT NULL,     -- CSS用カラーコード (#RRGGBB)
    emotion_id INTEGER NOT NULL,
    feeling_text VARCHAR(255) NOT NULL
);
