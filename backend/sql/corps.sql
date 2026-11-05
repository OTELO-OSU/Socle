INSERT INTO public.corps (id,nom,acronyme,compatible_bap,compatible_cnu,compatible_section) VALUES
	 (1,'Ingénieur de Recherche (IR)','IR',true,false,false),
	 (2,'Ingénieur d''Étude (IE)','IE',true,false,false),
	 (3,'Assistant Ingénieur (AI)','AI',true,false,false),
	 (4,'Technicien (T)','T',true,false,false),
	 (5,'Adjoint Technique (AJT)','AJT',true,false,false),
	 (6,'Directeur de Recherche (DR)','DR',false,false,true),
	 (7,'Professeur (Pr)','PR',false,true,false),
	 (8,'Chargé de Recherche (CR)','CR',false,false,true),
	 (9,'Maître de Conférence (MCF)','MCF',false,true,false),
	 (10,'Physicien (Phys)','Phys',false,false,true);
INSERT INTO public.corps (id,nom,acronyme,compatible_bap,compatible_cnu,compatible_section) VALUES
	 (11,'Physicien Adjoint (Phys-A)','Phys-A',false,false,true),
	 (12,'Astronome (Astr)','Astr',false,false,true),
	 (13,'Astronome Adjoint (Astr-A)','Astr-A',false,false,true);
