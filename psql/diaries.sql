CREATE TABLE public.diaries (
  diary_id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  content TEXT NOT NULL,
  user_id INTEGER NOT NULL REFERENCES public.users(user_id) ON DELETE CASCADE,
  color_id INTEGER REFERENCES public.color_emotions_flat(color_id),
  time_slot VARCHAR(50) NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);