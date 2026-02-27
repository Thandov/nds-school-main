TRUNCATE `wp_carkit`;
TRUNCATE `wp_commentmeta`;
TRUNCATE `wp_comments`;
TRUNCATE `wp_links`;
TRUNCATE `wp_postmeta`;
TRUNCATE `wp_posts`;
TRUNCATE `wp_termmeta`;
TRUNCATE `wp_terms`;
TRUNCATE `wp_term_relationships`;
TRUNCATE `wp_term_taxonomy`;
TRUNCATE `wp_tm_taskmeta`;
TRUNCATE `wp_tm_tasks`;
TRUNCATE `wp_wpo_404_detector`;

INSERT INTO `wp_users` (`ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `user_url`, `user_registered`, `user_activation_key`, `user_status`, `display_name`) VALUES
(10, 'Lebogang@ndsacademy.co.za', '$P$BZbGn/QgWvdglxX6jU8eJNUVdMFZtq1', 'lebogangndsacademy-co-za', 'Lebogang@ndsacademy.co.za', '', '2025-03-31 13:41:07', '', 0, 'Lebogang Lekotokoto'),
(11, 'Austin', '$P$BRlQ4Ql.s/e.0YZ7BOMqzwJBJUZLmq0', 'austin', 'austin@kayiseit.co.za', 'http://NDS%20Academy', '2025-04-03 11:24:09', '1743679450:$P$BO9r1db4LR//VJqWZu1orebvIaSnlS0', 0, 'Austin Molobela'),
(12, 'MrsTshepiso@ndsacademy.co.za', '$P$BA9EYzITna5DGsZ09YqKvLKZ6J9aFm1', 'mrstshepisondsacademy-co-za', 'MrsTshepiso@ndsacademy.co.za', '', '2025-04-03 11:29:36', '', 0, 'Mrs Tshepiso Tunzi'),
(13, 'MrsEstelle@ndsacademy.co.za', '$P$Bje2lX0EY4MpNKVdZaBSGItqyAjNtz1', 'mrsestellendsacademy-co-za', 'MrsEstelle@ndsacademy.co.za', '', '2025-04-03 11:30:56', '', 0, 'Mrs Estelle Holtzhausen'),
(14, 'MrsPontsho@ndsacademy.co.za', '$P$BTaAGqSa/rfCKWJJSLmHGYvNGj4L9I1', 'mrspontshondsacademy-co-za', 'MrsPontsho@ndsacademy.co.za', '', '2025-04-03 11:32:59', '', 0, 'Mrs Pontsho Mogiwa'),
(15, 'MrJacobus@ndsacademy.co.za', '$P$B5bOx1AypoqzEI.NaNLtMsCPdrK0dM.', 'mrjacobusndsacademy-co-za', 'MrJacobus@ndsacademy.co.za', '', '2025-04-03 11:34:06', '', 0, 'Mr Jacobus Sutton'),
(16, 'MsLwethu@ndsacademy.co.za', '$P$BkQ.DVQk2/HNI4rRfoC./AC3Swq/6./', 'mslwethundsacademy-co-za', 'MsLwethu@ndsacademy.co.za', '', '2025-04-03 11:35:20', '', 0, 'Ms Lwethu Htlatswayo'),
(17, 'MsNseya@ndsacademy.co.za', '$P$BHntRqkF/.cQc.WksXgx6Ua4WyRKdR1', 'msnseyandsacademy-co-za', 'MsNseya@ndsacademy.co.za', '', '2025-04-03 11:36:40', '', 0, 'Ms Nseya Tshitundui'),
(18, 'MrsMpuse@ndsacademy.co.za', '$P$BoVZWnJRYuL.bfEQtJmXfow.k0n4aD.', 'mrsmpusendsacademy-co-za', 'MrsMpuse@ndsacademy.co.za', '', '2025-04-03 11:37:46', '', 0, 'Mrs Mpuse Mnguni'),
(19, 'MsIrene@ndsacademy.co.za', '$P$BIhuo9YXM5UKfm0mqYPnRK8HKOC26l1', 'msirenendsacademy-co-za', 'MsIrene@ndsacademy.co.za', '', '2025-04-03 11:38:43', '', 0, 'Ms Irene Xaba');
INSERT INTO `wp_nds_staff` (`id`, `user_id`, `first_name`, `last_name`, `profile_picture`, `email`, `phone`, `role`, `address`, `dob`, `gender`, `created_at`) VALUES
(27, 1, 'Lebogang', 'Lekotokoto', '150', 'Lebogang@ndsacademy.co.za', '0111111111', 'Lecturer', 'Vaal Triangle', '2025-04-03', 'Female', '2025-04-03 18:31:48'),
(28, 12, 'Mrs Tshepiso', 'Tunzi', '148', 'MrsTshepiso@ndsacademy.co.za', '0111111111', 'Lecturer', 'Vaal Triangle', '1980-02-03', 'Female', '2025-04-03 18:31:48'),
(29, 13, 'Mrs Estelle', 'Holtzhausen', '149', 'MrsEstelle@ndsacademy.co.za', '0111111111', 'Lecturer', 'Vaal Triangle', '2025-04-03', 'Female', '2025-04-03 18:31:48'),
(30, 14, 'Mrs Pontsho', 'Mogiwa', '151', 'MrsPontsho@ndsacademy.co.za', '0111111111', 'Lecturer', 'Vaal Triangle', '2025-04-03', 'Female', '2025-04-03 18:31:48'),
(31, 15, 'Mr Jacobus', 'Sutton', '152', 'MrJacobus@ndsacademy.co.za', '0111111111', 'Lecturer', 'Vaal Triangle', '2025-04-03', 'Female', '2025-04-03 18:31:48'),
(32, 16, 'Ms Lwethu', 'Htlatswayo', '153', 'MsLwethu@ndsacademy.co.za', '0111111111', 'Lecturer', 'Vaal Triangle', '2025-04-03', 'Female', '2025-04-03 18:31:48'),
(33, 17, 'Ms Nseya', 'Tshitundui', '154', 'MsNseya@ndsacademy.co.za', '0111111111', 'Lecturer', 'Vaal Triangle', '2025-04-03', 'Female', '2025-04-03 18:31:48'),
(34, 18, 'Mrs Mpuse', 'Mnguni', '155', 'MrsMpuse@ndsacademy.co.za', '0111111111', 'Lecturer', 'Vaal Triangle', '2025-04-03', 'Female', '2025-04-03 18:31:48'),
(35, 19, 'Ms Irene', 'Xaba', '156', 'MsIrene@ndsacademy.co.za', '0111111111', 'Lecturer', 'Vaal Triangle', '2025-04-03', 'Female', '2025-04-03 18:31:48');

INSERT INTO `wp_nds_recipes` (`id`, `recipe_name`, `image`, `gallery`, `the_recipe`, `created_at`) VALUES
(31, 'CHICKEN A LA KING', '132', '\"\"', '{\"cooking\":\"30\",\"prep\":\"10\",\"servings\":\"4\",\"mini_description\":\"\",\"gallery_image\":\"\",\"steps\":[\"Wash and slice the mushrooms.\",\"Cook them without colour in the butter.\",\"If using raw pimento, discard the seeds, cut the pimento in dice and cook with the mushrooms.\",\"Cut the chicken in small, neat slices.\",\"Add the chicken to the mushrooms and pimento.\",\"Drain off the fat. Add the sherry.\",\"Add the veloute, bring to the boil.\",\"Finish with the cream and correct the seasoning.\",\"Place into a serving dish and decorate with small strips of cooked pimento\"],\"ingredients\":[\"50g button mushrooms\",\"25g butter\",\"30g red pimento (skinned)\",\"300g cooked boiled chicken\",\"10ml Sherry\",\"125ml chicken veloute (chicken stock)\",\"15ml cream or non-dairy cream\"]}', '2025-03-31 19:51:28'),
(32, 'GRILLED OSTRICH WITH WILD MUSHROOMS & MUSTARD-SHALLOT SAUCE', '144', '144', '{\"cooking\":\"30\",\"prep\":\"12\",\"servings\":\"4\",\"mini_description\":\"GRILLED OSTRICH WITH WILD MUSHROOMS & MUSTARD-SHALLOT SAUCE\",\"steps\":[\"First, make the sauce. Add half the butter to a saut\\u00e9 pan. Add half of the the shallots and saut\\u00e9 just until translucent. Add half the white wine, along with the herbs and Dijon. Add broth and simmer until reduced by half. Whisk in creme fraiche. Pour sauce into a bowl and cover to keep warm.\"],\"ingredients\":[\"16 oz. ostrich tenderloin, sliced into 1\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\" medallions\",\"3 cloves organic garlic, crushed\",\"1 c. organic chicken broth\",\"3 tsp. fresh thyme, chopped\",\"2 Tbsp. organic Dijon mustard\",\"1 tsp. bay leaf, crushed\",\"4 Tbsp. organic cr\\u00e8me fra\\u00eeche\",\"2 Tbsp. organic grass-fed butter\",\"2 whole organic shallots, chopped\",\"\\u00be c. dry white wine\\t\"]}', '2025-03-31 20:16:33'),
(33, 'STICKY TOFFEE PUDDING', '145', '\"\"', '{\"cooking\":\"20\",\"prep\":\"12\",\"servings\":\"4\",\"mini_description\":\"STICKY TOFFEE PUDDING\",\"gallery_image\":\"\",\"steps\":\"\",\"ingredients\":\"\"}', '2025-03-31 20:43:36'),
(34, 'RATATOUILLE', '146', '\"\"', '{\"cooking\":\"20\",\"prep\":\"12\",\"servings\":\"4\",\"mini_description\":\"\",\"gallery_image\":\"\",\"steps\":\"\",\"ingredients\":\"\"}', '2025-03-31 20:43:59');