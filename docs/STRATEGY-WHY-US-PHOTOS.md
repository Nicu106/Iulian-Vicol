# /por-que-nosotros, rebuilt from photographs: strategy (2026-10-09)

The two films go. The page tells the same story with the client's own photographs:
57 photos uploaded today (`photo_video_why_us/16 Mar 2025 – 29 Mar 2026/`, HEIC
converted), plus the arms-open welcome photo and the sunset portrait. **Every
text on the page stays exactly as it is. Only the layout and the motion change.**
Phone first: every decision below is made at 390×844 first, desktop second.

Storyboard (phone, real photos, real text in position):
`scratchpad/pics/board/storyboard.jpg` and one file per scene in `board/`.

---

## 1. What the photos are

- **Seven cars, each shot like a studio catalogue.**
  - Golf R-Line (black)
  - X1 (black)
  - C-Class (grey)
  - 4 Series Cabrio (navy, white leather)
  - Octavia RS estate (black, red calipers)
  - C-Class (black, AMG wheels)
  - C-Class (silver, AMG)
- **The same set every time:** one white panelled wall and pale asphalt, under even overcast light. That consistency is the asset. Every car stands in the same "studio", which reads as method, not luck.
- **Every car has a grammar of shots:** symmetric front, 3/4 front, profile, 3/4 rear, symmetric rear, a low wheel/door detail, an interior. So the page can cut like a film: wide → detail → interior, on the same car.
- **Three photos are people:** the arms-open welcome (keep the scene exactly as it is now), the sunset portrait, and the photo with three BMWs (not used here).

## 2. The one rule that makes it consistent

**The words live on the wall.** In almost every photo the top 30–45% is the plain
white wall. Headlines are set there in ink (#111C2E), as on the welcome photo,
which the client chose as "looks very, very good". Where a photo has no wall
(interiors, close details), the words go white on the darkest area of that frame,
measured, never with a heavy scrim. Reading paragraphs stay on the page's black,
as now.

## 3. Depth: how a still becomes cinema

1. **Text behind the car.** For the hero frames, the car is cut out of the photo. This was tested on the C-Class front and the Cabrio and the edges are clean. The stack is three layers: wall → headline → car. The car's roof overlaps the bottom of the letters, so the word sits *in* the scene, between the wall and the car.
   - These are the frames that make people stop.
   - Used 3 times only: the opening, "Seleccionados.", and the end.
2. **Parallax by layer.** As you scroll, the wall moves least, the words a little more, and the car most (scale 1.00 → 1.10). The camera seems to walk towards the car. This uses transform only, so it runs at 60 fps on phones.
3. **The cut, as in editing.**
   - **Match cuts:** symmetric front → symmetric front, so the grille stays in the same place on screen while the car changes.
   - **Push-in cuts:** wide → detail of the same car.
   - **Between chapters:** the black dip the page already uses.
4. **Slow settle.** Every frame arrives at 106% and settles to 100%, the same curve as the rest of the site.

## 4. Scene by scene (existing texts, new pictures)

| # | Text (unchanged) | Photo | Text position | Motion |
|---|---|---|---|---|
| 1 | Un coche bien elegido. / Y alguien que responde. | 4 Cabrio, symmetric front (IMG_1274) | on the wall, centred, **behind the car** | the words rise behind the roof; scroll = walk in |
| 2 | Seleccionados. | C-Class black, symmetric front (IMG_1871) | wall, behind the car | **match cut** from 1: same grille position |
| 3 | Revisados. | Octavia RS wheel, red caliper (IMG_1750) | ink on the pale asphalt, bottom | push-in from wide (IMG_1736) to the wheel |
| 4 | Preparados. | C-Class black 3/4 (IMG_1862) | wall, top-left | settle |
| 5 | Para que disfrutes / de algo especial. | Cabrio from above, white leather (IMG_1280) | white, bottom-left, on the dark body | slow descent onto the seats |
| 6 | Que te enamore el coche… (his paragraph, split by its own sentences) | "La motorización que querías." → vRS grille (IMG_1757) · "El color que te hace volver a mirarlo." → navy profile (IMG_1276) · "Ese equipamiento…" → harman/kardon door (IMG_1350) | each sentence on its own picture | three cuts, one per sentence |
| 7 | his four questions | "¿cómo lo han cuidado?" → Golf interior · "¿qué sabemos de sus kilómetros?" → BMW gauges · "¿ha tenido algún accidente?" → C-Class side reflections · **"¿quién me atenderá después?" → his portrait** | white on interiors, ink on the wall | the last question is answered by the next frame: him |
| 8 | Una pasión personal… + his text | sunset portrait (unchanged) | as now | as now |
| 9 | Te lo enseño antes de que vengas → El exterior. → El interior. → Y sus desperfectos. → Para que sepas… | **one car, all its pictures**: grey C-Class (IMG 154217–155058): 3/4 → profile → interior → a close look at the side → rear | wall / dark interior / asphalt | the car "turns" through its photos, which is exactly what the words promise |
| 10 | Lo que tú no ves a primera vista… | black C-Class door, mirror-smooth reflections (IMG_1877) | wall, left; the paint is the proof | slow lateral drift along the door |
| 11 | Las llaves son tuyas… | the arms-open welcome | **unchanged** | unchanged |
| 12 | La confianza se demuestra después de la entrega. | black C-Class, 3/4 rear (IMG_1880): the car leaving | wall, top-left | settle |
| 13 | figures, reviews | as now | | |
| 14 | Hay coches que llevas tiempo imaginando. ("un cabrio para disfrutar de la costa") | 4 Cabrio 3/4 (IMG_1273) | wall, **behind the car** | closes the loop with the opening car |

## 5. Phone rules (strict)

- All photos are 4:3 landscape. An upright phone shows only about 35% of the width, so every frame gets its own focal point (stored per image), never a centre crop:
  - Symmetric fronts carry the big moments, because they crop perfectly.
  - 3/4 shots are used where the crop still holds the whole front.
  - Details are used where any crop works.
- **Hero frames on phone:** the photo is anchored to the bottom (~65% of the screen) and the wall continues above it, so the words have their own space and the car still overlaps them (as in the storyboard).
- **Headline sizes are fitted to the width.** "Seleccionados." fills the line on a 320 phone as well as a 1920 screen.
- **Desktop and tablet:** the same scenes with the whole 4:3 frame. Text stays on the wall, with more air around it.

## 6. Weight and speed

- WebP derivatives at 640/1080/1600/2000 through the existing image pipeline. Car cut-outs are WebP with alpha, about 60–120 KB each.
- About 25 pictures on the whole page, about 3 MB on a phone and lazily loaded, against 11.5 MB of film today.
- The first frame (the opening) is preloaded; everything else loads one scene ahead.
- Without JS, or with reduced motion, it is a calm photo essay: the same pictures and text, nothing moving.

## 7. Build order

1. Convert and select the 25 frames. Set a focal point and a text zone per frame, for phone and desk.
2. Make the cut-outs for frames 1, 2 and 14, and touch up their masks by hand where needed (a red sliver at one bumper).
3. Scene engine: reuse the page's sticky stages and the black dip. The layers are wall, text and car.
4. Scenes 1–14, phone first, with a screenshot of every beat at 320/390/768/1440/1920.
5. Contrast audit on every line, plus the WebKit/Firefox/Chrome pass. Only then replace the live page. The films stay on disk for rollback.
