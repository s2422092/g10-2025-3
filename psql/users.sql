CREATE TABLE public.color_emotions_flat (
  id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id INTEGER NOT NULL,
  color_name VARCHAR(100) NOT NULL,
  emotion_id INTEGER NOT NULL,
  feeling_text VARCHAR(255) NOT NULL
);
