//
//  MainViewController.swift
//  Zoomtivity
//
//  Created by Callum Trounce on 17/02/2017.
//  Copyright © 2017 Zoomtivity. All rights reserved.
//

import Foundation
import UIKit
import SideMenu

class MainViewController: UIViewController {
    
    var leftDrawerMenu: LeftDrawerViewController!
    
    override func viewDidLoad() {
        super.viewDidLoad()
        configureHamburgerButton()
        
        let menuLeftNavigationController = storyboard!.instantiateViewController(withIdentifier: "LeftMenuNavigationController") as! UISideMenuNavigationController
        SideMenuManager.menuPresentMode = .menuSlideIn
        SideMenuManager.menuLeftNavigationController = menuLeftNavigationController
    }
    
    func configureHamburgerButton() {
        let hamburgerButton = UIButton()
        hamburgerButton.setImage(UIImage.init(named: "icon_hamburger_menu"), for: .normal)
        hamburgerButton.imageView?.contentMode = .scaleAspectFit
        hamburgerButton.addTarget(self, action: #selector(MainViewController.openHamburgerMenu) , for: .touchUpInside)
        
        if let navigationBar = self.navigationController?.navigationBar {
            hamburgerButton.frame.origin.x = 0
            hamburgerButton.frame.origin.y = 0
            hamburgerButton.frame.size.width = navigationBar.frame.size.width * 0.125
            hamburgerButton.frame.size.height = navigationBar.frame.size.height
            navigationBar.addSubview(hamburgerButton)
        }
    }
    
    func openHamburgerMenu() {
        if let leftMenu = SideMenuManager.menuLeftNavigationController {
            self.present(leftMenu, animated: true, completion: nil)
        }
    }
    
}
